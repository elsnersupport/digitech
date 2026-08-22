<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Model;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Resolve filters position save.
 */
class FilterSave
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * @var FilterSettingRepositoryInterface
     */
    private $filterSettingRepository;

    public function __construct(
        ProductAttributeRepositoryInterface $attributeRepository,
        WriterInterface $configWriter,
        ReinitableConfigInterface $reinitableConfig,
        FilterSettingRepositoryInterface $filterSettingRepository
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->configWriter = $configWriter;
        $this->reinitableConfig = $reinitableConfig;
        $this->filterSettingRepository = $filterSettingRepository;
    }

    /**
     * @param FilterData $filter
     */
    public function save(FilterData $filter): void
    {
        if ($filter->getAttributeCode() === FiltersProvider::CATEGORY_ATTRIBUTE_CODE) {
            $this->configWriter->save(
                $this->getConfigPath(FiltersProvider::CATEGORY_FILTER_CODE) . 'position',
                $filter->getPosition()
            );

            $this->reinitableConfig->reinit();

            return;
        }

        if ($this->isFilterAreAttribute($filter)) {
            $attribute = $this->attributeRepository->get($filter->getAttributeCode());
            $attribute->setPosition($filter->getPosition());
            $this->attributeRepository->save($attribute);

            return;
        }

        $this->configWriter->save(
            $this->getConfigPath($filter->getFilterCode()) . 'position',
            $filter->getPosition()
        );

        $this->reinitableConfig->reinit();
    }

    /**
     * @param FilterData $filter
     */
    public function saveAdditionalPositions(FilterData $filter): void
    {
        if ($this->isFilterAreAttribute($filter)) {
            $filterSettings = $this->filterSettingRepository->get(
                $filter->getAttributeCode(),
                FilterSettingInterface::ATTRIBUTE_CODE
            );

            $filterSettings->setTopPosition($filter->getTopPosition());
            $filterSettings->setSidePosition($filter->getSidePosition());

            $this->filterSettingRepository->save($filterSettings);

            return;
        }

        $path = $this->getConfigPath($filter->getFilterCode());

        $this->configWriter->save(
            $path . 'top_position',
            $filter->getTopPosition()
        );
        $this->configWriter->save(
            $path . 'side_position',
            $filter->getSidePosition()
        );

        $this->reinitableConfig->reinit();
    }

    private function getConfigPath(string $filterCode): string
    {
        return 'amshopby/' . $filterCode . '_filter/';
    }

    private function isFilterAreAttribute(FilterData $filter): bool
    {
        return $filter->getAttributeId() && $filter->getAttributeCode();
    }
}
