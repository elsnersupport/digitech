<?php

namespace Wizzy\Events\Plugin;

use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Wizzy\Search\Services\Catalogue\ProductsManager;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Psr\Log\LoggerInterface;

class SourceItemsSavePlugin
{
    private $indexer;

    public function __construct(
        private ProductsManager $productsManager,
        private Configurable $configurable,
        IndexerManager $indexerManager,
        private LoggerInterface $logger
    ) {
        $this->indexer = $indexerManager->getProductsIndexer();
    }

    /**
     * Plugin after MSI stock save
     */
    public function afterExecute(
        SourceItemsSaveInterface $subject,
        $result,
        array $sourceItems
    ) {
        try {
            // 🔹 Extract SKUs
            $skus = array_filter(array_map(
                fn($item) => $item instanceof SourceItemInterface ? $item->getSku() : null,
                $sourceItems
            ));

            if (empty($skus)) {
                return $result;
            }

            // 🔹 Get products
            $products = $this->productsManager->getProductsBySKUs($skus);
            $productIds = $this->productsManager->getProductIds($products);

            if (empty($productIds)) {
                return $result;
            }

            $productIdsToIndex = $productIds;

            foreach ($productIds as $productId) {

                // 🔹 Get parent products (for child SKUs)
                $parentProductIds = $this->configurable->getParentIdsByChild($productId);

                if (!empty($parentProductIds)) {
                    $productIdsToIndex = array_merge($productIdsToIndex, $parentProductIds);
                } else {
                    // 🔹 If parent/simple, also include children
                    $childProductIds = $this->configurable->getChildrenIds($productId);

                    if (!empty($childProductIds)) {
                        foreach ($childProductIds as $childGroup) {
                            $productIdsToIndex = array_merge($productIdsToIndex, $childGroup);
                        }
                    }
                }

                // Always include current product
                $productIdsToIndex[] = $productId;
            }

            // 🔹 Remove duplicates
            $productIdsToIndex = array_unique($productIdsToIndex);

            // 🔥 Trigger Wizzy Index
            if (!empty($productIdsToIndex) && !$this->indexer->isScheduled()) {
                $this->indexer->reindexList($productIdsToIndex);
            }

        } catch (\Exception $e) {
            $this->logger->error('Wizzy MSI Sync Error: ' . $e->getMessage());
        }

        return $result;
    }
}