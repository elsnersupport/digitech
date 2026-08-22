<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Model\ConfigFilter;

use Amasty\ShopByQuickConfig\Model\FilterData;
use Amasty\ShopByQuickConfig\Model\FilterDataFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class FilterListProvider
{
    private $itemsRegistry;

    /**
     * @var FilterDataFactory
     */
    private $filterDataFactory;

    /**
     * @var FilterCodeRegistry
     */
    private $filterCodeRegistry;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        FilterCodeRegistry $filterCodeRegistry,
        FilterDataFactory $filterDataFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->filterDataFactory = $filterDataFactory;
        $this->filterCodeRegistry = $filterCodeRegistry;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return FilterData[]
     */
    public function getItems(): array
    {
        $items = [];
        foreach ($this->filterCodeRegistry->getCustomFilterCodes() as $filterCode) {
            $items[$filterCode] = $this->getFilterItem($filterCode);
        }

        return $items;
    }

    /**
     * @param string $filterCode
     *
     * @return FilterData
     */
    public function getFilterItem(string $filterCode): FilterData
    {
        if (!isset($this->itemsRegistry[$filterCode])) {
            /** @var FilterData $item */
            $item = $this->filterDataFactory->create();

            $fieldsetConfig = $this->getFieldsetConfig($filterCode);
            $item->setData($fieldsetConfig);

            $item->setFilterCode($filterCode);
            $item->setIsEnabled((bool)(int) $this->getConfig($filterCode, 'enabled'));

            $this->itemsRegistry[$filterCode] = $item;
        }

        return $this->itemsRegistry[$filterCode];
    }

    /**
     * @param string $filterName
     * @param string $configName
     *
     * @return mixed
     */
    private function getConfig(string $filterName, string $configName)
    {
        return $this->scopeConfig->getValue(
            'amshopby/' . $filterName . '_filter/' . $configName,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * @param string $filterName
     *
     * @return array
     */
    private function getFieldsetConfig(string $filterName): array
    {
        return $this->scopeConfig->getValue(
            'amshopby/' . $filterName . '_filter',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }
}
