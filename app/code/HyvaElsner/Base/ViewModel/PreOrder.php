<?php

namespace HyvaElsner\Base\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class PreOrder implements ArgumentInterface
{
    public $bssHyvaHelper;
    public $stockConfigurationInterface;
    public $stockRegistryProviderInterface;
    public function __construct(
        \Hyva\BssPreOrder\ViewModels\Helper $bssHyvaHelper,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfigurationInterface,
        \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockRegistryProviderInterface
    ) {
        $this->bssHyvaHelper = $bssHyvaHelper;
        $this->stockConfigurationInterface = $stockConfigurationInterface;
        $this->stockRegistryProviderInterface = $stockRegistryProviderInterface;
    }
    public function getIsPreOrder($product)
    {
        if ($product->getTypeId() === 'grouped') {
            $childProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
            $simpleIndex = [];
            foreach ($childProducts as $key => $childProduct) {
                $childProductId = $childProduct->getId();
                $stockStatusG = $this->bssHyvaHelper->getHelper()->getIsInStock($childProductId);
                $fromDate =  $this->bssHyvaHelper->getHelper()->getPreOrderFromDate($childProductId);
                $toDate =  $this->bssHyvaHelper->getHelper()->getPreOrderToDate($childProductId);
                $isPreOderG = $this->bssHyvaHelper->getHelper()->isPreOrder($this->bssHyvaHelper->getHelper()->getPreOrder($childProductId), $stockStatusG);
                if ($isPreOderG && $this->bssHyvaHelper->getHelper()->isAvailablePreOrderFromFlatData($fromDate, $toDate)) {
                    $simpleIndex[$childProductId] = ["mess" => $this->bssHyvaHelper->getHelper()->getAvailabilityMessageByPid($childProductId), "button" => $this->bssHyvaHelper->getHelper()->getButton()];
                }
            }
            $isPreOder = count($childProducts) === count($simpleIndex);
            return $isPreOder;
        }

        if ($product->getTypeId() === 'simple') {
            $productId = $product->getId();
            $stockStatus = $this->getIsInStock($productId);
            $fromDate =  $this->bssHyvaHelper->getHelper()->getPreOrderFromDate($productId);
            $toDate =  $this->bssHyvaHelper->getHelper()->getPreOrderToDate($productId);
            $isPreOder = $this->bssHyvaHelper->getHelper()->isPreOrder($this->bssHyvaHelper->getHelper()->getPreOrder($productId), $stockStatus) && $this->bssHyvaHelper->getHelper()->isAvailablePreOrderFromFlatData($fromDate, $toDate);
            return $isPreOder;
        }
    }
    public function getIsInStock($productId)
    {
        $scopeId = $this->stockConfigurationInterface->getDefaultScopeId();
        $stockStatus = $this->stockRegistryProviderInterface->getStockStatus($productId, $scopeId);
        $status = $stockStatus->getData('stock_status');
        return $status;
    }
}
