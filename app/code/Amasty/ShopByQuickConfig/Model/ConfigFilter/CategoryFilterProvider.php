<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Model\ConfigFilter;

use Amasty\ShopbyBase\Model\FilterSetting\FilterResolver;
use Amasty\Shopby\Model\ConfigProvider;
use Amasty\ShopByQuickConfig\Model\FilterData;
use Amasty\ShopByQuickConfig\Model\FilterDataFactory;
use Amasty\ShopByQuickConfig\Model\FiltersProvider;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CategoryFilterProvider
{
    /**
     * @var FilterDataFactory
     */
    private $filterDataFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var FilterResolver
     */
    private $filterResolver;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        FilterDataFactory $filterDataFactory,
        ScopeConfigInterface $scopeConfig,
        ProductAttributeRepositoryInterface $attributeRepository,
        FilterResolver $filterResolver,
        ConfigProvider $configProvider
    ) {
        $this->filterDataFactory = $filterDataFactory;
        $this->scopeConfig = $scopeConfig;
        $this->attributeRepository = $attributeRepository;
        $this->filterResolver = $filterResolver;
        $this->configProvider = $configProvider;
    }

    public function get(): FilterData
    {
        $model = $this->filterDataFactory->create();
        $attribute = $this->attributeRepository->get(FiltersProvider::CATEGORY_ATTRIBUTE_CODE);
        $settings = $this->filterResolver->getFilterSetting($attribute);
        $model->addData($attribute->getData());
        $model->addData($settings->getData());
        $model->setPosition($this->configProvider->getCategoryPosition());
        $model->setIsEnabled($this->getIsEnabled());
        $model->setFilterCode(FiltersProvider::CATEGORY_ATTRIBUTE_CODE);
        $model->setLabel($attribute->getFrontendLabel());

        return $model;
    }

    /**
     * @return bool
     */
    public function getIsEnabled(): bool
    {
        return (bool)(int) $this->scopeConfig->getValue(
            'amshopby/category_filter/enabled',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }
}
