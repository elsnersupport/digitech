<?php

namespace Meetanshi\OrderTracking\Api;

interface OrderTrackingInterface
{
    /**
     * @param int $orderId
     * @param mixed $mailId
     * @return mixed
     */
    public function trackOrder($orderId, $mailId);
}
