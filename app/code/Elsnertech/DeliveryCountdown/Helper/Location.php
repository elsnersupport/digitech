<?php
namespace Elsnertech\DeliveryCountdown\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

class Location extends AbstractHelper
{
    protected $dataHelper;
    protected $curl;
    protected $customerSession;
    protected $remoteAddress;

    public function __construct(
        Context $context,
        Data $dataHelper,
        Curl $curl,
        CustomerSession $customerSession,
        RemoteAddress $remoteAddress
    ) {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->curl = $curl;
        $this->customerSession = $customerSession;
        $this->remoteAddress = $remoteAddress;
    }

    public function isAllowedLocation()
    {
        if (!$this->dataHelper->isLocationCheckEnabled()) {
            return true;
        }

        $allowedCountries = $this->dataHelper->getAllowedCountries();
        if (empty($allowedCountries)) {
            return true;
        }

        $visitorLocation = $this->getVisitorLocation();
        if (empty($visitorLocation)) {
            return false;
        }

        if (!in_array($visitorLocation['country_code'], $allowedCountries)) {
            return false;
        }

        $allowedRegions = $this->dataHelper->getAllowedRegions();
        if (!empty($allowedRegions)) {
            $visitorCity = isset($visitorLocation['city']) ? strtolower($visitorLocation['city']) : '';
            $visitorRegion = isset($visitorLocation['region']) ? strtolower($visitorLocation['region']) : '';
            
            foreach ($allowedRegions as $allowedRegion) {
                $allowedRegion = strtolower(trim($allowedRegion));
                if ($visitorCity === $allowedRegion || $visitorRegion === $allowedRegion) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    protected function getVisitorLocation()
    {
        if ($this->dataHelper->isIpDetectionEnabled()) {
            $ipLocation = $this->getLocationByIp();
            if (!empty($ipLocation)) {
                return $ipLocation;
            }
        }

        if ($this->dataHelper->isFallbackToSessionEnabled()) {
            return $this->getLocationFromCustomer();
        }

        return [];
    }

    protected function getLocationByIp()
    {
        try {
            // CLOUDFLARE SUPPORT: Try Cloudflare headers first
            if (isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {
                $cfLocation = $this->getLocationFromCloudflare();
                if (!empty($cfLocation)) {
                    return $cfLocation;
                }
            }

            // Get real IP (works with Cloudflare)
            $ip = $this->getRealIpAddress();
            
            if ($this->isLocalIp($ip)) {
                return [
                    'country_code' => 'AE',
                    'country' => 'United Arab Emirates',
                    'city' => 'Dubai',
                    'region' => 'Dubai'
                ];
            }

            $apiKey = $this->dataHelper->getGeolocationApiKey();
            $url = !empty($apiKey) 
                ? "https://ipapi.co/{$ip}/json/?key={$apiKey}"
                : "https://ipapi.co/{$ip}/json/";

            $this->curl->get($url);
            $response = $this->curl->getBody();
            
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['country_code'])) {
                    return [
                        'country_code' => $data['country_code'] ?? '',
                        'country' => $data['country_name'] ?? '',
                        'city' => $data['city'] ?? '',
                        'region' => $data['region'] ?? ''
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->_logger->error('Delivery Countdown IP Detection Error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get location from Cloudflare headers (CLOUDFLARE SUPPORT)
     *
     * @return array
     */
    protected function getLocationFromCloudflare()
    {
        $location = [];

        if (isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {
            $countryCode = $_SERVER['HTTP_CF_IPCOUNTRY'];
            
            // Cloudflare returns 'XX' for unknown countries
            if ($countryCode !== 'XX') {
                $location['country_code'] = $countryCode;
                $location['country'] = $this->getCountryName($countryCode);
                
                // Cloudflare doesn't provide city in free plan
                // We'll infer city as the country's main city for now
                if ($countryCode === 'AE') {
                    $location['city'] = 'Dubai';
                    $location['region'] = 'Dubai';
                }
            }
        }

        return $location;
    }

    /**
     * Get real IP address - Cloudflare compatible (CLOUDFLARE SUPPORT)
     *
     * @return string
     */
    protected function getRealIpAddress()
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',    // Cloudflare
            'HTTP_X_FORWARDED_FOR',      // Standard proxy
            'HTTP_X_REAL_IP',            // Nginx proxy
            'REMOTE_ADDR'                // Fallback
        ];

        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $this->remoteAddress->getRemoteAddress();
    }

    /**
     * Get country name from code
     *
     * @param string $code
     * @return string
     */
    protected function getCountryName($code)
    {
        $countries = [
            'AE' => 'United Arab Emirates',
            'SA' => 'Saudi Arabia',
            'QA' => 'Qatar',
            'BH' => 'Bahrain',
            'KW' => 'Kuwait',
            'OM' => 'Oman'
        ];

        return $countries[$code] ?? $code;
    }

    protected function getLocationFromCustomer()
    {
        try {
            if ($this->customerSession->isLoggedIn()) {
                $customer = $this->customerSession->getCustomer();
                $address = $customer->getDefaultShippingAddress();
                
                if (!$address) {
                    $address = $customer->getDefaultBillingAddress();
                }

                if ($address) {
                    return [
                        'country_code' => $address->getCountryId(),
                        'country' => $address->getCountry(),
                        'city' => $address->getCity(),
                        'region' => $address->getRegion()
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->_logger->error('Delivery Countdown Customer Location Error: ' . $e->getMessage());
        }

        return [];
    }

    protected function isLocalIp($ip)
    {
        return in_array($ip, ['127.0.0.1', '::1', 'localhost']) || 
               strpos($ip, '192.168.') === 0 || 
               strpos($ip, '10.') === 0;
    }
}