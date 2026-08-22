<?php

namespace Digi\OrderSync\Observer;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class CustomerRegistered implements ObserverInterface
{
    private const WEBHOOK_URL = 'https://n8n.digitech.tv/webhook/magento-customer';

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
            $customer = $observer->getEvent()->getCustomer();
            if (!$customer instanceof CustomerInterface || !$customer->getId()) {
                return;
            }

            $payload = [
                'entity_id' => (int)$customer->getId(),
                'website_id' => $customer->getWebsiteId() !== null ? (int)$customer->getWebsiteId() : null,
                'store_id' => $customer->getStoreId() !== null ? (int)$customer->getStoreId() : null,
                'group_id' => $customer->getGroupId() !== null ? (int)$customer->getGroupId() : null,
                'email' => $customer->getEmail(),
                'firstname' => $customer->getFirstname(),
                'middlename' => $customer->getMiddlename(),
                'lastname' => $customer->getLastname(),
                'prefix' => $customer->getPrefix(),
                'suffix' => $customer->getSuffix(),
                'dob' => $customer->getDob(),
                'gender' => $customer->getGender(),
                'taxvat' => $customer->getTaxvat(),
                'created_at' => $customer->getCreatedAt(),
                'updated_at' => $customer->getUpdatedAt(),
                'default_billing' => $customer->getDefaultBilling(),
                'default_shipping' => $customer->getDefaultShipping(),
                'confirmation' => $customer->getConfirmation(),
                'disable_auto_group_change' => $customer->getDisableAutoGroupChange(),
                'addresses' => $this->extractAddresses($customer),
                'custom_attributes' => $this->extractCustomAttributes($customer->getCustomAttributes()),
                'raw_data' => $this->normalizeValue($customer->__toArray()),
            ];

            $this->curl->addHeader('Content-Type', 'application/json');
            $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($body === false) {
                throw new \RuntimeException('Failed to encode customer payload: ' . json_last_error_msg());
            }

            $this->curl->post(self::WEBHOOK_URL, $body);

            $status = (int)$this->curl->getStatus();
            $logContext = [
                'customer_id' => (int)$customer->getId(),
                'customer_email' => $customer->getEmail(),
                'http_status' => $status,
                'response' => $this->curl->getBody(),
            ];

            if ($status >= 200 && $status < 300) {
                $this->logger->info('Digi Customer Sync webhook sent', $logContext);
            } else {
                $this->logger->warning('Digi Customer Sync webhook returned non-success status', $logContext);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Digi Customer Sync webhook failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function extractAddresses(CustomerInterface $customer): array
    {
        $addresses = [];

        foreach ($customer->getAddresses() ?? [] as $address) {
            if (!$address instanceof AddressInterface) {
                continue;
            }

            $addresses[] = [
                'id' => $address->getId() !== null ? (int)$address->getId() : null,
                'customer_id' => $address->getCustomerId() !== null ? (int)$address->getCustomerId() : null,
                'firstname' => $address->getFirstname(),
                'middlename' => $address->getMiddlename(),
                'lastname' => $address->getLastname(),
                'company' => $address->getCompany(),
                'street' => $address->getStreet(),
                'city' => $address->getCity(),
                'region' => $address->getRegion() ? $address->getRegion()->getRegion() : null,
                'region_code' => $address->getRegion() ? $address->getRegion()->getRegionCode() : null,
                'region_id' => $address->getRegion() ? $address->getRegion()->getRegionId() : null,
                'postcode' => $address->getPostcode(),
                'country_id' => $address->getCountryId(),
                'telephone' => $address->getTelephone(),
                'fax' => $address->getFax(),
                'vat_id' => $address->getVatId(),
                'default_billing' => (bool)$address->isDefaultBilling(),
                'default_shipping' => (bool)$address->isDefaultShipping(),
                'custom_attributes' => $this->extractCustomAttributes($address->getCustomAttributes()),
                'raw_data' => $this->normalizeValue($address->__toArray()),
            ];
        }

        return $addresses;
    }

    private function extractCustomAttributes(?array $attributes): array
    {
        $data = [];

        foreach ($attributes ?? [] as $attribute) {
            $data[$attribute->getAttributeCode()] = $this->normalizeValue($attribute->getValue());
        }

        return $data;
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
