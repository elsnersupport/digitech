<?php

namespace Meetanshi\OrderTracking\Model\Api;

use Psr\Log\LoggerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\Registry;
use Magento\Framework\Exception\LocalizedException;
use Meetanshi\OrderTracking\Helper\Data as HelperData;
use Magento\Sales\Model\Order;

class OrderTracking
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;
    protected $layoutFactory;
    protected $orderFactory;
    protected $registry;
    protected $helper;
    protected $order;

    public function __construct(
        LoggerInterface $logger,
        CartRepositoryInterface $cartRepository,
        LayoutFactory $layoutFactory,
        OrderFactory $orderFactory,
        Registry $registry,
        HelperData $helper,
        Order $order
    ) {
        $this->cartRepository = $cartRepository;
        $this->logger = $logger;
        $this->layoutFactory = $layoutFactory;
        $this->orderFactory = $orderFactory;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->order = $order;
    }

    /**
     * @inheritdoc
     */

    public function trackOrder($orderId, $mailId)
    {
        try {
            $order = $this->orderFactory->create()->load($orderId, 'increment_id');
            $orderEmail = $order->getCustomerEmail();

            if ($orderEmail == trim((string)$mailId)) {
                $this->registry->register('current_order', $order);
            } else {
                $this->registry->register('current_order', $this->orderFactory->create());
            }

            $order = $this->registry->registry('current_order');

            $shipTrack = [];
            if ($order->getId()) {
                $shipments = $order->getShipmentsCollection();
                foreach ($shipments as $shipment) {
                    $increment_id = $shipment->getIncrementId();
                    $tracks = $shipment->getTracksCollection();
                    $trackingInfos = [];
                    foreach ($tracks as $track) {
                        $trackingInfos[] = $track->getData();
                    }
                    $shipTrack[$increment_id] = $trackingInfos;
                }

                $_results = $shipTrack;
                $url = "";
                if (count($_results) > 0) {
                    foreach ($_results as $shipid => $_result) {

                        foreach ($_result as $track) {
                        }

                        $urlLink = $this->helper->getActiveCarriers();
                        foreach ($urlLink as $data) {
                            if ($data->getTitle() == $track['title']) {
                                $url = $data->getUrl();
                            }
                        }
                    }
                    $array = [
                        "order_status" => $order->getStatusLabel(),
                        "shipment_id" => $shipid,
                        "tracker_title" => $track['title'],
                        "tracker_number" => $track['track_number'],
                        "tracker_link" => $url,
                        'success' => true,
                        'message' => 'Track Order Successfully'
                    ];
                } else {
                    $array = [
                        'success' => false,
                        'message' => $this->helper->customMessage()
                    ];
                }

                $response = [$array];
            } else {
                $array = [
                    'success' => false,
                    'message' => $this->helper->customMessage()
                ];
                $response = [$array];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
            $this->logger->info($e->getMessage());
        }
        return $response;
    }
}
