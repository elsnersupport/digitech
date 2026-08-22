<?php

namespace Meetanshi\OrderTracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Meetanshi\OrderTracking\Helper\Data;
use Magento\Framework\Event\Observer;

/**
 * Class EmailTemplate
 * @package Meetanshi\OrderTracking\Observer
 */
class EmailTemplate implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * EmailTemplate constructor.
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if ($observer->getBlock() && $observer->getBlock()->getTemplate() ==
            'Magento_Sales::email/shipment/track.phtml') {
            if ($this->helper->isEnabled()) {
                $observer->getBlock()->setTemplate('Meetanshi_OrderTracking::track.phtml');
            }
        }
        return $this;
    }
}
