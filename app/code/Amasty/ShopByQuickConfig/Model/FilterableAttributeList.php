<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Model;

use Amasty\ShopbyBase\Helper\FilterSetting;
use Magento\Catalog\Model\Layer\Category\FilterableAttributeList as CatalogAttributeList;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;

/**
 * Using the same collection as catalog category do.
 *   Collection should have same fields, joins, filters
 *   otherwise MYSQL can cache different result
 *   witch may vary from frontend if position (main sorting field for catalog attributes) are the same.
 *   Because Mysql sorts randomly if order field values are the same.
 */
class FilterableAttributeList extends CatalogAttributeList
{
    /**
     * Add filters to attribute collection.
     *
     * @param Collection $collection
     * @return Collection
     */
    protected function _prepareAttributeCollection($collection)
    {
        $collection->setItemObjectClass(FilterData::class);
        parent::_prepareAttributeCollection($collection);

        $filterCode = sprintf('CONCAT(\'%s\',  %s)', FilterSetting::ATTR_PREFIX, FilterData::ATTRIBUTE_CODE);

        // without reset columns, otherwise sorting result may be differ with frontend
        $collection->getSelect()
            ->columns([FilterData::ATTRIBUTE_ID, FilterData::ATTRIBUTE_CODE, FilterData::LABEL => 'frontend_label'])
            ->columns([FilterData::FILTER_CODE => $filterCode])
            ->order(['position ASC']);

        return $collection;
    }
}
