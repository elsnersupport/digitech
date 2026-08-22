<?php

namespace Meetanshi\OrderTracking\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class SaveOrderTracking
 * @package Meetanshi\OrderTracking\Observer
 */
class SaveOrderTracking implements ObserverInterface
{
    /**
     * @var int
     */
    private $trackLinkCounter = 1;

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->trackLinkCounter > 1) {
            return $this;
        }

        $order = $observer->getEvent()->getOrder();
        $this->trackLinkCounter++;
        if ($order->getOrdertrackingLink() == null) {
            $orderTrackLink = substr(hash('SHA512', microtime()), rand(0, 26), 6);
            $order->setOrdertrackingLink($orderTrackLink);
            $order->save();
        }
    }
}
