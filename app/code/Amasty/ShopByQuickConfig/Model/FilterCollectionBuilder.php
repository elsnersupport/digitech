<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Model;

use Amasty\ShopByQuickConfig\Model\ConfigFilter\CategoryFilterProvider;
use Amasty\ShopByQuickConfig\Model\ConfigFilter\FilterListProvider;
use Amasty\ShopByQuickConfig\Model\ResourceModel\FilterAggregation;
use Amasty\ShopByQuickConfig\Model\ResourceModel\FilterAggregation\GridCollection;
use Amasty\ShopByQuickConfig\Model\ResourceModel\FilterAggregation\GridCollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Config as EavConfig;

class FilterCollectionBuilder
{
    /**
     * @var Collection|null
     */
    private $attributeCollection;

    /**
     * @var GridCollectionFactory
     */
    private $filterCollectionFactory;

    /**
     * @var CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var ResourceModel\FilterAggregation
     */
    private $filterAggregationResource;

    /**
     * @var ConfigFilter\FilterListProvider
     */
    private $filterListProvider;

    /**
     * @var ConfigFilter\CategoryFilterProvider
     */
    private $categoryFilterProvider;

    /**
     * @var EavConfig
     */
    private $attributeConfig;

    public function __construct(
        GridCollectionFactory $filterCollectionFactory,
        FilterListProvider $filterListProvider,
        FilterAggregation $filterAggregationResource,
        CollectionFactory $collectionFactory,
        CategoryFilterProvider $categoryFilterProvider,
        EavConfig $attributeConfig
    ) {
        $this->filterCollectionFactory = $filterCollectionFactory;
        $this->attributeCollectionFactory = $collectionFactory;
        $this->filterAggregationResource = $filterAggregationResource;
        $this->filterListProvider = $filterListProvider;
        $this->categoryFilterProvider = $categoryFilterProvider;
        $this->attributeConfig = $attributeConfig;
    }

    /**
     * @param Collection $attributeCollection
     */
    public function addAttributeCollection(Collection $attributeCollection): void
    {
        $this->attributeCollection = $attributeCollection;
    }

    /**
     * Create temporary table of filters and return Collection
     *
     * @return GridCollection
     */
    public function build(): GridCollection
    {
        $this->filterAggregationResource->createTable();

        $this->filterAggregationResource->insertCustomFilter($this->categoryFilterProvider->get());

        foreach ($this->filterListProvider->getItems() as $item) {
            $this->filterAggregationResource->insertCustomFilter($item);
        }

        $attributeCollection = $this->getAttributeCollection();
        $this->fulfillByAttributes($attributeCollection);

        $this->reset();

        return $this->filterCollectionFactory->create();
    }

    private function getAttributeCollection(): Collection
    {
        if ($this->attributeCollection === null) {
            $this->attributeCollection = $this->attributeCollectionFactory->create();
            $this->attributeCollection->addFieldToFilter(
                AttributeInterface::FRONTEND_INPUT,
                ['in' => ['boolean', 'select', 'multiselect', 'price']]
            );

            $lockedAttributeCodes = $this->getLockedAttributeCodes();

            $this->attributeCollection->addFieldToFilter(
                AttributeInterface::ATTRIBUTE_CODE,
                ['nin' => $lockedAttributeCodes]
            );
        }

        return $this->attributeCollection;
    }

    public function reset(): void
    {
        $this->attributeCollection = null;
    }

    /**
     * @param Collection $attributeCollection
     */
    private function fulfillByAttributes(Collection $attributeCollection): void
    {
        $select = $attributeCollection->getSelect();
        $this->filterAggregationResource->insertAttributesSelect($select);
    }

    /**
     * Return Attributes with locked 'is_filterable' property.
     *
     * @return string[]
     */
    private function getLockedAttributeCodes(): array
    {
        $lockedAttributesData = $this->attributeConfig->getEntityAttributesLockedFields(Product::ENTITY);

        $lockedAttributeCodes = [];
        foreach ($lockedAttributesData as $attributeCode => $lockedFields) {
            if (\in_array('is_filterable', $lockedFields, true)) {
                $lockedAttributeCodes[] = $attributeCode;
            }
        }

        return $lockedAttributeCodes;
    }
}
