<?php

namespace Infibeam\Ccavenue\Plugin;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Psr\Log\LoggerInterface;
use Infibeam\Ccavenue\Model\Order\EmailSender as CcavenueEmailSender;


class OrderEmailSenderPlugin
{
    protected $logger;
    protected $ccavenueEmailSender;
    protected $coreOrderSender;



    public function __construct(LoggerInterface $logger,CcavenueEmailSender $ccavenueEmailSender,OrderSender $coreOrderSender)
    {
        $this->logger = $logger;
        $this->ccavenueEmailSender = $ccavenueEmailSender;
        $this->coreOrderSender = $coreOrderSender;

    }

    public function aroundSend(OrderSender $subject, callable $proceed, Order $order, $forceSyncMode = false)
    {

        $payment = $order->getPayment();
        $transactionStatus = $payment->getAdditionalInformation('ccavenue_transaction_status');
        $this->logger->info('CCavenue transaction status -'. $transactionStatus);
        // Check if the payment method is CCAvenue and the transaction status
        if ($payment->getMethod() == 'ccavenue') {
            if ($transactionStatus == 'Success') {
                return $this->ccavenueEmailSender->sendSuccessEmail($order, $forceSyncMode);
            } else {
                if($transactionStatus != ''){
                    return $proceed($order, $forceSyncMode);
                    // return false;
                }
            }
        } else {
            // For other payment methods, use core Magento sender
            $this->logger->info('Non-CCavenue payment method - sending core Magento email');
            return $proceed($order, $forceSyncMode);
        }
    }

}
