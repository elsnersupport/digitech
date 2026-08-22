<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Ui\DataProvider;

use Amasty\Shopby\Model\Source\FilterPlacedBlock;
use Amasty\ShopByQuickConfig\Model\FilterData;
use Amasty\ShopByQuickConfig\Model\FiltersProvider;
use Magento\Framework\Api\Filter;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

class QuickForm implements DataProviderInterface
{
    public const KEY_SIDE_ITEMS = 'side_items';
    public const KEY_TOP_ITEMS = 'top_items';

    /**
     * Data Provider name
     *
     * @var string
     */
    protected $name;

    /**
     * Data Provider Primary Identifier name
     *
     * @var string
     */
    protected $primaryFieldName;

    /**
     * Data Provider Request Parameter Identifier name
     *
     * @var string
     */
    protected $requestFieldName;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * Provider configuration data
     *
     * @var array
     */
    protected $data = [];

    /**
     * @var FiltersProvider
     */
    private $filtersProvider;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        FiltersProvider $filtersProvider,
        array $meta = [],
        array $data = []
    ) {
        $this->name = $name;
        $this->primaryFieldName = $primaryFieldName;
        $this->requestFieldName = $requestFieldName;
        $this->filtersProvider = $filtersProvider;
        $this->meta = $meta;
        $this->data = $data;
    }

    public function getData(): array
    {
        $items = $this->filtersProvider->getFilterItems();

        $sideItems = $topItems = [];

        foreach ($items as $item) {
            $itemData = $item->toArray(
                [
                    FilterData::SIDE_POSITION,
                    FilterData::TOP_POSITION,
                    FilterData::POSITION,
                    FilterData::ATTRIBUTE_ID,
                    FilterData::ATTRIBUTE_CODE,
                    FilterData::FILTER_CODE,
                    FilterData::LABEL
                ]
            );

            switch ($item->getBlockPosition()) {
                case FilterPlacedBlock::POSITION_SIDEBAR:
                    unset($itemData[FilterData::SIDE_POSITION], $itemData[FilterData::TOP_POSITION]);
                    $sideItems[] = $itemData;
                    break;
                case FilterPlacedBlock::POSITION_TOP:
                    unset($itemData[FilterData::SIDE_POSITION], $itemData[FilterData::TOP_POSITION]);
                    $topItems[] = $itemData;
                    break;
                case FilterPlacedBlock::POSITION_BOTH:
                    $itemData[FilterData::POSITION] = $itemData[FilterData::SIDE_POSITION] ?? 0;
                    unset($itemData[FilterData::SIDE_POSITION]);
                    $sideItems[] = $itemData;
                    $itemData[FilterData::POSITION] = $itemData[FilterData::TOP_POSITION] ?? 0;
                    unset($itemData[FilterData::TOP_POSITION]);
                    $topItems[] = $itemData;
                    break;
            }
        }

        usort($sideItems, [$this, 'sortArrayPosition']);
        usort($topItems, [$this, 'sortArrayPosition']);

        $this->uniquePosition($sideItems);
        $this->uniquePosition($topItems);

        $data = [self::KEY_SIDE_ITEMS => $sideItems, self::KEY_TOP_ITEMS => $topItems];

        return [null => $data];
    }

    /**
     * @param array $itemA
     * @param array $itemB
     *
     * @return int
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) used in usort
     */
    private function sortArrayPosition(array $itemA, array $itemB): int
    {
        return $itemA[FilterData::POSITION] <=> $itemB[FilterData::POSITION];
    }

    private function uniquePosition(array &$items): void
    {
        $position = 1;
        foreach ($items as &$item) {
            $item[FilterData::POSITION] = $position++;
        }
    }

    /**
     * Current provider can't be filtered.
     *
     * @param Filter $filter
     *
     * @return null|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFilter(Filter $filter)
    {
        return null;
    }

    /**
     * Get Data Provider name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get primary field name
     *
     * @return string
     */
    public function getPrimaryFieldName()
    {
        return $this->primaryFieldName;
    }

    /**
     * Get field name in request
     *
     * @return string
     */
    public function getRequestFieldName()
    {
        return $this->requestFieldName;
    }

    /**
     * Return Meta
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Get field Set meta info
     *
     * @param string $fieldSetName
     * @return array
     */
    public function getFieldSetMetaInfo($fieldSetName)
    {
        return $this->meta[$fieldSetName] ?? [];
    }

    /**
     * Return fields meta info
     *
     * @param string $fieldSetName
     * @return array
     */
    public function getFieldsMetaInfo($fieldSetName)
    {
        return $this->meta[$fieldSetName]['children'] ?? [];
    }

    /**
     * Return field meta info
     *
     * @param string $fieldSetName
     * @param string $fieldName
     * @return array
     */
    public function getFieldMetaInfo($fieldSetName, $fieldName)
    {
        return $this->meta[$fieldSetName]['children'][$fieldName] ?? [];
    }

    /**
     * Get config data
     *
     * @return mixed
     */
    public function getConfigData()
    {
        return $this->data['config'] ?? [];
    }

    /**
     * Set data
     *
     * @param mixed $config
     * @return void
     */
    public function setConfigData($config)
    {
        $this->data['config'] = $config;
    }

    /**
     * Current provider can't be ordered.
     *
     * @param $field
     * @param $direction
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addOrder($field, $direction)
    {
        return null;
    }

    /**
     * Current provider can't be ordered.
     *
     * @param $offset
     * @param $size
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setLimit($offset, $size)
    {
        return null;
    }

    public function getSearchCriteria()
    {
        return null;
    }

    public function getSearchResult()
    {
        return null;
    }
}
