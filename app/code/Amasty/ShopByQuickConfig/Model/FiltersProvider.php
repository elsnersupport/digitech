<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Model;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting\Collection as FilterSettingCollection;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting\CollectionFactory as FilterSettingCollectionFactory;
use Amasty\ShopByQuickConfig\Model\ConfigFilter\CategoryFilterProvider;
use Amasty\ShopByQuickConfig\Model\ConfigFilter\FilterListProvider;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as AttributeCollection;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\NoSuchEntityException;

class FiltersProvider
{
    public const CATEGORY_ATTRIBUTE_CODE = 'category_ids';

    public const CATEGORY_FILTER_CODE = 'category';

    /**
     * @var FilterData[]
     */
    private $itemsCache = [];

    /**
     * @var FilterSettingCollectionFactory
     */
    private $filterCollectionFactory;

    /**
     * @var FilterableAttributeList
     */
    private $filterableAttributeList;

    /**
     * @var ConfigFilter\FilterListProvider
     */
    private $filterListProvider;

    /**
     * @var CategoryFilterProvider
     */
    private $categoryFilterProvider;

    public function __construct(
        FilterSettingCollectionFactory $filterCollectionFactory,
        FilterableAttributeList $filterableAttributeList,
        FilterListProvider $filterListProvider,
        CategoryFilterProvider $categoryFilterProvider
    ) {
        $this->filterCollectionFactory = $filterCollectionFactory;
        $this->filterableAttributeList = $filterableAttributeList;
        $this->filterListProvider = $filterListProvider;
        $this->categoryFilterProvider = $categoryFilterProvider;
    }

    /**
     * Clear local registry and variables cache
     */
    public function reset(): void
    {
        $this->itemsCache = [];
    }

    /**
     * Get layered navigation filters items data.
     *
     * @return FilterData[]
     */
    public function getFilterItems(): array
    {
        $this->loadFilterItems();

        return array_values($this->itemsCache);
    }

    /**
     * Load layered navigation filters items data.
     */
    private function loadFilterItems(): void
    {
        if (!empty($this->itemsCache)) {
            return;
        }

        if ($this->categoryFilterProvider->getIsEnabled()) {
            $this->itemsCache[self::CATEGORY_ATTRIBUTE_CODE] = $this->categoryFilterProvider->get();
        }

        $this->loadCustomFilters();
        $this->loadAttributeFilters();
    }

    private function loadCustomFilters(): void
    {
        foreach ($this->filterListProvider->getItems() as $item) {
            if ($item->getIsEnabled()) {
                $this->itemsCache[$item->getFilterCode()] = $item;
            }
        }
    }

    /**
     * Load product attributes of layered navigation filters wit additional positions.
     */
    private function loadAttributeFilters(): void
    {
        $attributeCollection = $this->getAttributeCollection();
        $filterCodes = $attributeCollection->getColumnValues(FilterData::FILTER_CODE);
        $blockPositions = $this->getFiltersBlockData($filterCodes);

        /** @var FilterData $filter */
        foreach ($attributeCollection->getItems() as $filter) {
            $filterCode = $filter->getFilterCode();
            if (isset($blockPositions[$filterCode])) {
                $filter->addData($blockPositions[$filterCode]);
            }
            $this->itemsCache[$filterCode] = $filter;
        }
    }

    /**
     * Get attributes Collection which can be used in layered navigation filters.
     */
    private function getAttributeCollection(): AttributeCollection
    {
        return $this->filterableAttributeList->getList();
    }

    /**
     * Get additional position data of layered navigation filters.
     *
     * @param string[] $filterCodes
     *
     * @return array array(
     *                  array('block_position' => int, 'top_position' => int, 'side_position' => int),
     *              ...)
     */
    private function getFiltersBlockData(array $filterCodes): array
    {
        /** @var FilterSettingCollection $filterCollection */
        $filterCollection = $this->filterCollectionFactory->create();
        $filterCollection->addFieldToFilter('filter_code', ['in' => $filterCodes]);
        $filterCollection->getSelect()
            ->setPart(Select::COLUMNS, [])
            ->columns(
                [
                    FilterSettingInterface::FILTER_SETTING_ID,
                    FilterSettingInterface::FILTER_CODE,
                    FilterSettingInterface::BLOCK_POSITION,
                    FilterSettingInterface::TOP_POSITION,
                    FilterSettingInterface::SIDE_POSITION,
                ]
            );

        $blockPositions = [];
        foreach ($filterCollection->getData() as $data) {
            $filterCode = $data[FilterSettingInterface::FILTER_CODE];

            $blockData[FilterSettingInterface::BLOCK_POSITION] = (int) $data[FilterSettingInterface::BLOCK_POSITION];
            $blockData[FilterSettingInterface::TOP_POSITION] = (int) $data[FilterSettingInterface::TOP_POSITION];
            $blockData[FilterSettingInterface::SIDE_POSITION] = (int) $data[FilterSettingInterface::SIDE_POSITION];

            $blockPositions[$filterCode] = $blockData;
        }

        return $blockPositions;
    }

    /**
     * Get Filter Item data model by filter code.
     *
     * @throws NoSuchEntityException
     */
    public function getItemByCode(string $filterCode): FilterData
    {
        $this->loadFilterItems();

        if (!isset($this->itemsCache[$filterCode])) {
            throw new NoSuchEntityException(__('Can not load Filter with code "%1"', $filterCode));
        }

        return $this->itemsCache[$filterCode];
    }
}
