<?php

namespace Meetanshi\OrderTracking\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Carrier
 * @package Meetanshi\OrderTracking\Model\ResourceModel
 */
class Carrier extends AbstractDb
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('meetanshi_custom_carrier', 'id');
    }
}
