<?php

namespace Meetanshi\OrderTracking\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Block\Order\Email\Shipment;
use Magento\Framework\Registry;
use Magento\Sales\Block\Order\Email\Items;
use Meetanshi\OrderTracking\Helper\Data as HelperData;

/**
 * Class OrderSendlink
 * @package Meetanshi\OrderTracking\Block
 */
class OrderSendlink extends Items
{
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * OrderSendlink constructor.
     * @param Context $context
     * @param Registry $registry
     * @param HelperData $helper
     * @param array $data
     */
    public function __construct(Context $context, Registry $registry, HelperData $helper, array $data = [])
    {
        $this->registry = $registry;
        $this->helper = $helper;
        parent::__construct($context, $data);
        $this->setTemplate('Meetanshi_OrderTracking::order/items.phtml');
    }

    /**
     * @return mixed
     */
    public function getOrderTracking()
    {
        return $this->registry->registry('current_order');
    }

    /**
     * @return HelperData
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
