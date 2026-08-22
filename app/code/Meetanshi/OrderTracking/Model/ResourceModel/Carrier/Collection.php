<?php

namespace Meetanshi\OrderTracking\Model\ResourceModel\Carrier;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Meetanshi\OrderTracking\Model\ResourceModel\Carrier
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'meetanshi_custom_carrier_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'meetanshi_custom_carrier_collection';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(
            \Meetanshi\OrderTracking\Model\Carrier::class,
            \Meetanshi\OrderTracking\Model\ResourceModel\Carrier::class
        );
    }
}
