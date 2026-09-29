<?php
/**
 * Elsnertech_DealsCategory
 *
 * @category    Elsnertech
 * @package     Elsnertech_DealsCategory
 * @author      Elsnertech
 * @copyright   Copyright (c) 2026 Elsnertech
 */
declare(strict_types=1);

namespace Elsnertech\DealsCategory\Console\Command;

use Elsnertech\DealsCategory\Model\Config;
use Elsnertech\DealsCategory\Model\DealsCategorySync;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs the deals category sync by hand; --dry-run shows what would change without saving.
 */
class SyncDealsCategory extends Command
{
    public const COMMAND_NAME = 'elsnertech:deals-category:sync';
    private const OPTION_DRY_RUN = 'dry-run';

    /**
     * @var State
     */
    private $appState;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DealsCategorySync
     */
    private $dealsCategorySync;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @param State $appState
     * @param Config $config
     * @param DealsCategorySync $dealsCategorySync
     * @param ProductResource $productResource
     */
    public function __construct(
        State $appState,
        Config $config,
        DealsCategorySync $dealsCategorySync,
        ProductResource $productResource
    ) {
        $this->appState = $appState;
        $this->config = $config;
        $this->dealsCategorySync = $dealsCategorySync;
        $this->productResource = $productResource;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Sync the deals category with products whose special price is active today')
            ->addOption(
                self::OPTION_DRY_RUN,
                null,
                InputOption::VALUE_NONE,
                'Show which products would be added or removed without saving the category'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->getAreaCode();
        } catch (LocalizedException $e) {
            // Same area as the scheduled job, so both run the same save observers.
            $this->appState->setAreaCode(Area::AREA_CRONTAB);
        }

        $dryRun = (bool)$input->getOption(self::OPTION_DRY_RUN);
        if (!$this->config->isEnabled()) {
            $output->writeln('<comment>The scheduled sync is disabled; running on request only.</comment>');
        }

        try {
            $result = $this->dealsCategorySync->execute($dryRun);
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        $output->writeln(sprintf(
            'Deals category %d: %d products qualify today.',
            $result->getCategoryId(),
            count($result->getDealProductIds())
        ));
        $this->writeProducts($output, 'To add', $result->getAddedProductIds());
        $this->writeProducts($output, 'To remove', $result->getRemovedProductIds());

        if (!$result->hasChanges()) {
            $output->writeln('<info>Already up to date; the category was not saved.</info>');
        } elseif ($dryRun) {
            $output->writeln('<comment>Dry run: nothing was saved.</comment>');
        } else {
            $output->writeln('<info>Category saved.</info>');
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Print a count and the ID and SKU of each product.
     *
     * @param OutputInterface $output
     * @param string $label
     * @param int[] $productIds
     * @return void
     */
    private function writeProducts(OutputInterface $output, string $label, array $productIds): void
    {
        $output->writeln(sprintf('%s: %d', $label, count($productIds)));
        if ($productIds === []) {
            return;
        }

        $skus = [];
        foreach ($this->productResource->getProductsSku($productIds) as $row) {
            $skus[(int)$row['entity_id']] = $row['sku'];
        }
        foreach ($productIds as $productId) {
            $output->writeln(sprintf('  %d  %s', $productId, $skus[$productId] ?? ''));
        }
    }
}
