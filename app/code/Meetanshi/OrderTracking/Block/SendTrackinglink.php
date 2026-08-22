<?php

namespace Meetanshi\OrderTracking\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Block\Order\Email\Shipment;
use Magento\Sales\Block\Order\Email\Shipment\Items;
use Magento\Framework\Registry;
use Meetanshi\OrderTracking\Helper\Data as HelperData;

/**
 * Class SendTrackinglink
 * @package Meetanshi\OrderTracking\Block
 */
class SendTrackinglink extends Items
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
     * SendTrackinglink constructor.
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
        $this->setTemplate('Meetanshi_OrderTracking::shipment/items.phtml');
    }

    /**
     * @return mixed
     */
    public function getOrderTracking()
    {
        return $this->getOrder();
    }

    /**
     * @return mixed
     */
    public function getShipment()
    {
        return $this->registry->registry('current_shipment');
    }

    /**
     * @return HelperData
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
