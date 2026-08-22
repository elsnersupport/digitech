<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Model\ResourceModel\FilterAggregation;

use Amasty\ShopByQuickConfig\Model\FilterData;
use Amasty\ShopByQuickConfig\Model\ResourceModel\FilterAggregation;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Filters tmp table collection aggregation
 */
class GridCollection extends AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(FilterData::class, FilterAggregation::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
