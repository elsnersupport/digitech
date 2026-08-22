<?php

namespace Meetanshi\OrderTracking\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Carrier
 * @package Meetanshi\OrderTracking\Model
 */
class Carrier extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Meetanshi\OrderTracking\Model\ResourceModel\Carrier::class);
    }
}
