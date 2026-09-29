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

namespace Elsnertech\DealsCategory\Model;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Makes the configured deals category contain exactly the products that are a deal today.
 *
 * The category is only saved when products need to be added or removed, so an unchanged run
 * costs two reads and does not flush the category's page cache. Magento cron already stops
 * this job overlapping itself, and the sync is idempotent, so it takes no lock of its own.
 */
class DealsCategorySync
{
    /**
     * Position for newly added deals. The old script put every product at 1; products that
     * stay in the category keep whatever position they already have.
     */
    private const NEW_PRODUCT_POSITION = 1;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DealProductProvider
     */
    private $dealProductProvider;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var CategoryResource
     */
    private $categoryResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Config $config
     * @param DealProductProvider $dealProductProvider
     * @param CategoryFactory $categoryFactory
     * @param CategoryResource $categoryResource
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        DealProductProvider $dealProductProvider,
        CategoryFactory $categoryFactory,
        CategoryResource $categoryResource,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->dealProductProvider = $dealProductProvider;
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Sync the deals category. With $dryRun the changes are worked out but not saved.
     *
     * @param bool $dryRun
     * @return SyncResult
     * @throws LocalizedException
     */
    public function execute(bool $dryRun = false): SyncResult
    {
        $categoryId = $this->config->getCategoryId();
        if ($categoryId === null) {
            throw new LocalizedException(
                __('The deals category ID is not configured (%1).', Config::XML_PATH_CATEGORY_ID)
            );
        }

        $category = $this->loadCategory($categoryId);
        $storeId = (int)$this->storeManager->getDefaultStoreView()->getId();
        $dealProductIds = $this->dealProductProvider->getProductIds($storeId);

        // Read straight from the resource: Category::getProductsPosition() caches on the model.
        $currentPositions = $this->categoryResource->getProductsPosition($category);
        $currentProductIds = array_map('intval', array_keys($currentPositions));

        $addedProductIds = array_values(array_diff($dealProductIds, $currentProductIds));
        $removedProductIds = array_values(array_diff($currentProductIds, $dealProductIds));
        $hasChanges = $addedProductIds !== [] || $removedProductIds !== [];

        $saved = false;
        if ($hasChanges && !$dryRun) {
            $positions = [];
            foreach ($dealProductIds as $productId) {
                $positions[$productId] = isset($currentPositions[$productId])
                    ? (int)$currentPositions[$productId]
                    : self::NEW_PRODUCT_POSITION;
            }
            $category->setPostedProducts($positions);
            // Save through the model, as the admin category page does, so plugins on
            // Category::save() run (Wizzy search only syncs product changes made in store 0).
            $category->save();
            $saved = true;

            $this->logger->info(sprintf(
                'Deals category %d synced: %d products, %d added, %d removed.',
                $categoryId,
                count($dealProductIds),
                count($addedProductIds),
                count($removedProductIds)
            ));
        }

        return new SyncResult($categoryId, $dealProductIds, $addedProductIds, $removedProductIds, $saved);
    }

    /**
     * Load a fresh copy of the category in the admin (all store views) scope.
     *
     * A fresh load rather than the repository's cached instance: cron runs many jobs in one
     * process, and a stale cached "products_position" would make the save diff wrong.
     *
     * @param int $categoryId
     * @return Category
     * @throws NoSuchEntityException
     */
    private function loadCategory(int $categoryId): Category
    {
        $category = $this->categoryFactory->create();
        $category->setStoreId(Store::DEFAULT_STORE_ID);
        $category->load($categoryId);
        if (!$category->getId()) {
            throw new NoSuchEntityException(
                __('The deals category with ID "%1" does not exist.', $categoryId)
            );
        }

        return $category;
    }
}
