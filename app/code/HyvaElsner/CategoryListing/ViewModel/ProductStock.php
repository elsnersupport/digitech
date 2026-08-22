<?php

namespace HyvaElsner\CategoryListing\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class ProductStock implements ArgumentInterface
{
    protected StockRegistryInterface $stockRegistry;

    public function __construct(
        StockRegistryInterface $stockRegistry
    ) {
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Check if product is in stock based on product ID
     *
     * @param int $productId
     * @return bool
     */
    public function isProductInStock(int $productId): bool
    {
        $stockItem = $this->stockRegistry->getStockItem($productId);
        return $stockItem->getIsInStock();
    }
}
