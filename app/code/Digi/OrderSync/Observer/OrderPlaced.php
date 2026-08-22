<?php

namespace Digi\OrderSync\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class OrderPlaced implements ObserverInterface
{
    private const WEBHOOK_URL = 'https://n8n.digitech.tv/webhook/magento-new-order';

    private Curl $curl;
    private LoggerInterface $logger;

    public function __construct(
        Curl $curl,
        LoggerInterface $logger
    ) {
        $this->curl = $curl;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            if (!$order || !$order->getEntityId()) {
                return;
            }

            $store = $order->getStore();
            $payment = $order->getPayment();
            $billingAddress = $order->getBillingAddress();
            $shippingAddress = $order->getShippingAddress();

            $payload = [
                'entity_id' => (int)$order->getEntityId(),
                'increment_id' => $order->getIncrementId(),
                'quote_id' => (int)$order->getQuoteId(),
                'status' => $order->getStatus(),
                'state' => $order->getState(),
                'store' => [
                    'store_id' => (int)$order->getStoreId(),
                    'website_id' => $store ? (int)$store->getWebsiteId() : null,
                    'store_code' => $store ? $store->getCode() : null,
                    'store_name' => $store ? $store->getName() : null,
                    'store_group_id' => $store ? (int)$store->getStoreGroupId() : null,
                ],
                'customer_id' => $order->getCustomerId() ? (int)$order->getCustomerId() : null,
                'customer_is_guest' => (bool)$order->getCustomerIsGuest(),
                'customer_group_id' => $order->getCustomerGroupId() !== null ? (int)$order->getCustomerGroupId() : null,
                'customer_email' => $order->getCustomerEmail(),
                'customer_firstname' => $order->getCustomerFirstname(),
                'customer_lastname' => $order->getCustomerLastname(),
                'customer_middlename' => $order->getCustomerMiddlename(),
                'customer_prefix' => $order->getCustomerPrefix(),
                'customer_suffix' => $order->getCustomerSuffix(),
                'customer_dob' => $order->getCustomerDob(),
                'customer_note' => $order->getCustomerNote(),
                'coupon_code' => $order->getCouponCode(),
                'applied_rule_ids' => $order->getAppliedRuleIds(),
                'payment' => [
                    'method' => $payment ? $payment->getMethod() : null,
                    'method_title' => $payment && $payment->getMethodInstance()
                        ? $payment->getMethodInstance()->getTitle()
                        : null,
                    'amount_ordered' => $payment ? (float)$payment->getAmountOrdered() : null,
                    'base_amount_ordered' => $payment ? (float)$payment->getBaseAmountOrdered() : null,
                    'shipping_amount' => $payment ? (float)$payment->getShippingAmount() : null,
                    'base_shipping_amount' => $payment ? (float)$payment->getBaseShippingAmount() : null,
                    'last_trans_id' => $payment ? $payment->getLastTransId() : null,
                    'cc_type' => $payment ? $payment->getCcType() : null,
                    'cc_last4' => $payment ? $payment->getCcLast4() : null,
                    'additional_information' => $payment
                        ? $this->normalizeValue($payment->getAdditionalInformation())
                        : [],
                ],
                'shipping' => [
                    'method' => $order->getShippingMethod(),
                    'description' => $order->getShippingDescription(),
                    'amount' => (float)$order->getShippingAmount(),
                    'base_amount' => (float)$order->getBaseShippingAmount(),
                    'tax_amount' => (float)$order->getShippingTaxAmount(),
                    'discount_amount' => (float)$order->getShippingDiscountAmount(),
                    'incl_tax' => (float)$order->getShippingInclTax(),
                ],
                'billing_address' => $this->extractAddress($billingAddress),
                'shipping_address' => $this->extractAddress($shippingAddress),
                'totals' => [
                    'grand_total' => (float)$order->getGrandTotal(),
                    'base_grand_total' => (float)$order->getBaseGrandTotal(),
                    'subtotal' => (float)$order->getSubtotal(),
                    'base_subtotal' => (float)$order->getBaseSubtotal(),
                    'subtotal_incl_tax' => (float)$order->getSubtotalInclTax(),
                    'base_subtotal_incl_tax' => (float)$order->getBaseSubtotalInclTax(),
                    'discount_amount' => (float)$order->getDiscountAmount(),
                    'base_discount_amount' => (float)$order->getBaseDiscountAmount(),
                    'discount_tax_compensation_amount' => (float)$order->getDiscountTaxCompensationAmount(),
                    'tax_amount' => (float)$order->getTaxAmount(),
                    'base_tax_amount' => (float)$order->getBaseTaxAmount(),
                    'total_due' => (float)$order->getTotalDue(),
                    'base_total_due' => (float)$order->getBaseTotalDue(),
                    'total_paid' => (float)$order->getTotalPaid(),
                    'base_total_paid' => (float)$order->getBaseTotalPaid(),
                    'total_qty_ordered' => (float)$order->getTotalQtyOrdered(),
                    'total_item_count' => (int)$order->getTotalItemCount(),
                    'weight' => (float)$order->getWeight(),
                ],
                'grand_total' => (float)$order->getGrandTotal(),
                'subtotal' => (float)$order->getSubtotal(),
                'currency' => $order->getOrderCurrencyCode(),
                'base_currency' => $order->getBaseCurrencyCode(),
                'store_currency' => $order->getStoreCurrencyCode(),
                'remote_ip' => $order->getRemoteIp(),
                'x_forwarded_for' => $order->getXForwardedFor(),
                'is_virtual' => (bool)$order->getIsVirtual(),
                'created_at' => $order->getCreatedAt(),
                'updated_at' => $order->getUpdatedAt(),
                'items' => $this->extractItems($order),
            ];

            $this->curl->addHeader('Content-Type', 'application/json');
            $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($body === false) {
                throw new \RuntimeException('Failed to encode order payload: ' . json_last_error_msg());
            }

            $this->curl->post(self::WEBHOOK_URL, $body);

            $status = (int)$this->curl->getStatus();
            $logContext = [
                'order_id' => $order->getIncrementId(),
                'http_status' => $status,
                'response' => $this->curl->getBody()
            ];

            if ($status >= 200 && $status < 300) {
                $this->logger->info('Digi Order Sync webhook sent', $logContext);
            } else {
                $this->logger->warning('Digi Order Sync webhook returned non-success status', $logContext);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Digi Order Sync webhook failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function extractItems($order): array
    {
        $items = [];

        foreach ($order->getAllVisibleItems() as $item) {
            $items[] = [
                'item_id' => (int)$item->getItemId(),
                'product_id' => (int)$item->getProductId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'product_type' => $item->getProductType(),
                'qty' => (float)$item->getQtyOrdered(),
                'price' => (float)$item->getPrice(),
                'base_price' => (float)$item->getBasePrice(),
                'original_price' => (float)$item->getOriginalPrice(),
                'row_total' => (float)$item->getRowTotal(),
                'base_row_total' => (float)$item->getBaseRowTotal(),
                'row_total_incl_tax' => (float)$item->getRowTotalInclTax(),
                'tax_amount' => (float)$item->getTaxAmount(),
                'discount_amount' => (float)$item->getDiscountAmount(),
                'weight' => (float)$item->getWeight(),
                'product_options' => $this->normalizeValue($item->getProductOptions()),
            ];
        }

        return $items;
    }

    private function extractAddress($address): ?array
    {
        if (!$address) {
            return null;
        }

        return [
            'entity_id' => $address->getEntityId() ? (int)$address->getEntityId() : null,
            'address_type' => $address->getAddressType(),
            'firstname' => $address->getFirstname(),
            'middlename' => $address->getMiddlename(),
            'lastname' => $address->getLastname(),
            'company' => $address->getCompany(),
            'street' => $address->getStreet(),
            'city' => $address->getCity(),
            'region' => $address->getRegion(),
            'region_code' => $address->getRegionCode(),
            'postcode' => $address->getPostcode(),
            'country_id' => $address->getCountryId(),
            'telephone' => $address->getTelephone(),
            'fax' => $address->getFax(),
            'email' => $address->getEmail(),
        ];
    }

    private function normalizeValue($value)
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->normalizeValue($item);
            }

            return $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string)$value;
        }

        return get_debug_type($value);
    }
}
