<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Model\FilterAggregation;

use Amasty\ShopByQuickConfig\Model\FilterData;
use Amasty\ShopByQuickConfig\Model\FiltersProvider;
use Amasty\ShopByQuickConfig\Model\ResourceModel\FilterAggregation;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Filter property "is filterable" manager
 */
class ManageFilterable
{
    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    public function __construct(
        WriterInterface $configWriter,
        ProductAttributeRepositoryInterface $attributeRepository,
        ReinitableConfigInterface $reinitableConfig
    ) {
        $this->configWriter = $configWriter;
        $this->attributeRepository = $attributeRepository;
        $this->reinitableConfig = $reinitableConfig;
    }

    /**
     * Save Filterable property of the filter.
     * Show or Hide the filter in the Layered Navigation.
     *
     * @param FilterData $filter
     */
    public function execute(FilterData $filter): void
    {
        if ((int)$filter->getData(FilterAggregation::IS_CUSTOM_FILTER)) {
            $this->saveConfigFilter($filter);
            $this->reinitableConfig->reinit();
        } else {
            $this->saveAttributeFilter($filter);
        }
    }

    /**
     * @param FilterData $filter
     */
    private function saveAttributeFilter(FilterData $filter): void
    {
        $attribute = $this->attributeRepository->get($filter->getAttributeCode());
        $attribute->setIsFilterable($filter->getData(FilterAggregation::IS_FILTERABLE));
        $this->attributeRepository->save($attribute);
    }

    /**
     * @param FilterData $filter
     */
    private function saveConfigFilter(FilterData $filter): void
    {
        $attributeCode = $filter->getAttributeCode();
        if ($attributeCode === FiltersProvider::CATEGORY_ATTRIBUTE_CODE) {
            $attributeCode = FiltersProvider::CATEGORY_FILTER_CODE;
        }
        $this->configWriter->save(
            $this->getConfigPath($attributeCode),
            $filter->getData(FilterAggregation::IS_FILTERABLE)
        );
    }

    /**
     * @param string $filterCode
     *
     * @return string
     */
    private function getConfigPath(string $filterCode): string
    {
        return 'amshopby/' . $filterCode . '_filter/enabled';
    }
}
