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

namespace Elsnertech\DealsCategory\Cron;

use Elsnertech\DealsCategory\Model\Config;
use Elsnertech\DealsCategory\Model\DealsCategorySync;
use Magento\Framework\Exception\LocalizedException;

/**
 * Scheduled deals category sync; replaces the server cron that ran pub/deal_products_assign.php.
 *
 * Failures are left to propagate so Magento records them against the job in cron_schedule.
 */
class SyncDealsCategory
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var DealsCategorySync
     */
    private $dealsCategorySync;

    /**
     * @param Config $config
     * @param DealsCategorySync $dealsCategorySync
     */
    public function __construct(Config $config, DealsCategorySync $dealsCategorySync)
    {
        $this->config = $config;
        $this->dealsCategorySync = $dealsCategorySync;
    }

    /**
     * Sync the deals category when the scheduled sync is enabled.
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $this->dealsCategorySync->execute();
    }
}
