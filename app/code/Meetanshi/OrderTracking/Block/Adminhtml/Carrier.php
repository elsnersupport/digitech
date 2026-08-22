<?php
namespace Meetanshi\OrderTracking\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class Carrier
 * @package Meetanshi\OrderTracking\Block\Adminhtml
 */
class Carrier extends Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_carrier';
        $this->_blockGroup = 'Meetanshi_OrderTracking';
        $this->_headerText = __('Methods');
        $this->_addButtonLabel = __('Add Carrier');
        parent::_construct();
    }
}
