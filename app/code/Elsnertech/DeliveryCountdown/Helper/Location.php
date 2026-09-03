<?php
/**
 * Elsnertech_DeliveryCountdown
 *
 * @category    Elsnertech
 * @package     Elsnertech_DeliveryCountdown
 * @author      Elsnertech
 * @copyright   Copyright (c) 2025 Elsnertech
 */

namespace Elsnertech\DeliveryCountdown\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

class Location extends AbstractHelper
{
    /**
     * Cloudflare request headers.
     *
     * Only CF-IPCountry is present by default. The city, region and region code
     * arrive once "Add visitor location headers" is switched on under Managed
     * Transforms in the Cloudflare dashboard; without it the emirate cannot be
     * known and this helper deliberately reports no city rather than guessing.
     */
    private const HEADER_COUNTRY = 'CF-IPCountry';
    private const HEADER_CITY = 'CF-IPCity';
    private const HEADER_REGION = 'CF-Region';
    /**
     * Cloudflare's documentation and its actual output have not always agreed on
     * whether the subdivision code header is hyphenated, so both spellings are
     * accepted. The city and region headers carry the match that matters; this is
     * only an extra chance to recognise the emirate.
     */
    private const HEADERS_REGION_CODE = ['CF-Region-Code', 'CF-RegionCode'];
    private const HEADER_CONNECTING_IP = 'CF-Connecting-IP';

    protected $dataHelper;
    protected $curl;
    protected $customerSession;
    protected $remoteAddress;

    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * Resolved once per request. The result feeds the page cache vary string, so
     * it can be asked for several times while a single page is built.
     *
     * @var bool|null
     */
    private $isAllowed;

    /**
     * @var array|null
     */
    private $visitorLocation;

    public function __construct(
        Context $context,
        Data $dataHelper,
        Curl $curl,
        CustomerSession $customerSession,
        RemoteAddress $remoteAddress,
        HttpRequest $request
    ) {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->curl = $curl;
        $this->customerSession = $customerSession;
        $this->remoteAddress = $remoteAddress;
        $this->request = $request;
    }

    /**
     * Check whether the visitor is somewhere the countdown should be offered
     *
     * @return bool
     */
    public function isAllowedLocation()
    {
        if ($this->isAllowed === null) {
            $this->isAllowed = $this->resolveIsAllowedLocation();
        }

        return $this->isAllowed;
    }

    /**
     * @return bool
     */
    private function resolveIsAllowedLocation()
    {
        if (!$this->dataHelper->isLocationCheckEnabled()) {
            return true;
        }

        // Checked before anything geographic so the people working on the site can
        // preview a region restricted countdown without opening it to shoppers.
        if ($this->isPreviewAddress()) {
            return true;
        }

        $allowedCountries = $this->dataHelper->getAllowedCountries();
        if (empty($allowedCountries)) {
            return true;
        }

        $visitorLocation = $this->getVisitorLocation();
        if (empty($visitorLocation['country_code'])) {
            return false;
        }

        if (!in_array($visitorLocation['country_code'], $allowedCountries, true)) {
            return false;
        }

        $allowedRegions = $this->dataHelper->getAllowedRegions();
        if (empty($allowedRegions)) {
            return true;
        }

        return $this->matchesAllowedRegion($visitorLocation, $allowedRegions);
    }

    /**
     * Whether the request comes from an address configured to always see the widget
     *
     * @return bool
     */
    private function isPreviewAddress()
    {
        $previewIps = $this->dataHelper->getPreviewIps();
        if (empty($previewIps)) {
            return false;
        }

        $ip = $this->getRealIpAddress();
        if ($ip === '') {
            return false;
        }

        foreach ($previewIps as $pattern) {
            if ($this->ipMatches($ip, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Match an address against a single address or an IPv4 CIDR range
     *
     * @param string $ip
     * @param string $pattern
     * @return bool
     */
    private function ipMatches($ip, $pattern)
    {
        if (strpos($pattern, '/') === false) {
            return $ip === $pattern;
        }

        list($subnet, $bits) = array_pad(explode('/', $pattern, 2), 2, '');
        $bits = (int) $bits;

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        // Ranges are IPv4 only; an IPv6 visitor simply will not match one.
        if ($ipLong === false || $subnetLong === false || $bits < 0 || $bits > 32) {
            return false;
        }

        if ($bits === 0) {
            return true;
        }

        $mask = (-1 << (32 - $bits)) & 0xFFFFFFFF;

        return (($ipLong & 0xFFFFFFFF) & $mask) === (($subnetLong & 0xFFFFFFFF) & $mask);
    }

    /**
     * Compare the visitor's city, region and region code against the allowed list
     *
     * All three are considered because Cloudflare reports the emirate under a
     * romanised ISO subdivision name that rarely matches how it is typed in the
     * admin: Sharjah arrives as "Ash Shariqah" and Abu Dhabi as "Abu Zaby", while
     * the city header carries the familiar spelling.
     *
     * @param array $visitorLocation
     * @param string[] $allowedRegions
     * @return bool
     */
    private function matchesAllowedRegion(array $visitorLocation, array $allowedRegions)
    {
        $candidates = [];
        foreach (['city', 'region', 'region_code'] as $key) {
            $normalised = $this->normalise($visitorLocation[$key] ?? '');
            if ($normalised !== '') {
                $candidates[] = $normalised;
            }
        }

        // Nothing to compare with. Failing closed keeps the widget from promising
        // a delivery window to somewhere it does not cover.
        if (empty($candidates)) {
            return false;
        }

        foreach ($allowedRegions as $allowedRegion) {
            if (in_array($this->normalise($allowedRegion), $candidates, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $value
     * @return string
     */
    private function normalise($value)
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', (string) $value)));
    }

    /**
     * @return array
     */
    protected function getVisitorLocation()
    {
        if ($this->visitorLocation !== null) {
            return $this->visitorLocation;
        }

        $location = [];

        if ($this->dataHelper->isIpDetectionEnabled()) {
            $location = $this->getLocationByIp();
        }

        if (!$this->isUsable($location) && $this->dataHelper->isFallbackToSessionEnabled()) {
            $fromCustomer = $this->getLocationFromCustomer();
            if ($this->isUsable($fromCustomer)) {
                $location = $fromCustomer;
            }
        }

        return $this->visitorLocation = $location;
    }

    /**
     * @return array
     */
    protected function getLocationByIp()
    {
        $cloudflare = $this->getLocationFromCloudflare();
        if ($this->isUsable($cloudflare)) {
            return $cloudflare;
        }

        // Cloudflare knew the country but not the city, and the configuration
        // needs one. Fall back to the lookup service before giving up.
        $fromApi = $this->getLocationFromApi();

        return $this->isUsable($fromApi) ? $fromApi : $cloudflare;
    }

    /**
     * Whether a resolved location answers everything the configuration asks of it
     *
     * @param array $location
     * @return bool
     */
    private function isUsable(array $location)
    {
        if (empty($location['country_code'])) {
            return false;
        }

        if (empty($this->dataHelper->getAllowedRegions())) {
            return true;
        }

        return !empty($location['city']) || !empty($location['region']) || !empty($location['region_code']);
    }

    /**
     * Read the visitor's location straight off the Cloudflare request headers
     *
     * @return array
     */
    protected function getLocationFromCloudflare()
    {
        $countryCode = strtoupper($this->getHeaderValue(self::HEADER_COUNTRY));

        // Cloudflare sends XX when it cannot place the address, and T1 for Tor.
        if ($countryCode === '' || $countryCode === 'XX' || $countryCode === 'T1') {
            return [];
        }

        return [
            'country_code' => $countryCode,
            'country' => $this->getCountryName($countryCode),
            'city' => $this->getHeaderValue(self::HEADER_CITY),
            'region' => $this->getHeaderValue(self::HEADER_REGION),
            'region_code' => $this->getFirstHeaderValue(self::HEADERS_REGION_CODE),
        ];
    }

    /**
     * Return the first of several candidate headers that carries a value
     *
     * @param string[] $names
     * @return string
     */
    private function getFirstHeaderValue(array $names)
    {
        foreach ($names as $name) {
            $value = $this->getHeaderValue($name);

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    /**
     * @param string $name
     * @return string
     */
    private function getHeaderValue($name)
    {
        $value = $this->request->getHeader($name);

        if ($value === false || $value === null || $value === '') {
            return '';
        }

        $value = (string) $value;

        // Cloudflare percent-encodes city names that are not plain ASCII.
        return trim(strpos($value, '%') !== false ? rawurldecode($value) : $value);
    }

    /**
     * @return array
     */
    protected function getLocationFromApi()
    {
        try {
            $ip = $this->getRealIpAddress();

            if ($ip === '') {
                return [];
            }

            $apiKey = $this->dataHelper->getGeolocationApiKey();
            $url = !empty($apiKey)
                ? "https://ipapi.co/{$ip}/json/?key={$apiKey}"
                : "https://ipapi.co/{$ip}/json/";

            $this->curl->setTimeout(2);
            $this->curl->get($url);
            $response = $this->curl->getBody();

            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['country_code'])) {
                    return [
                        'country_code' => strtoupper($data['country_code']),
                        'country' => $data['country_name'] ?? '',
                        'city' => $data['city'] ?? '',
                        'region' => $data['region'] ?? '',
                        'region_code' => $data['region_code'] ?? '',
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->_logger->error('Delivery Countdown IP Detection Error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get the visitor's real IP address, looking through Cloudflare and proxies
     *
     * @return string
     */
    protected function getRealIpAddress()
    {
        $headers = [
            self::HEADER_CONNECTING_IP,  // Cloudflare
            'X-Forwarded-For',           // Standard proxy
            'X-Real-IP',                 // Nginx proxy
        ];

        foreach ($headers as $header) {
            $value = $this->getHeaderValue($header);

            if ($value === '') {
                continue;
            }

            if (strpos($value, ',') !== false) {
                $parts = explode(',', $value);
                $value = trim($parts[0]);
            }

            $isPublic = filter_var(
                $value,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );

            if ($isPublic) {
                return $value;
            }
        }

        $remote = $this->remoteAddress->getRemoteAddress();

        // A private address means the request never left the network, so there is
        // nothing a geolocation service could tell us about it.
        return filter_var(
            $remote,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) ? (string) $remote : '';
    }

    /**
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
            'OM' => 'Oman',
            'IN' => 'India'
        ];

        return $countries[$code] ?? $code;
    }

    /**
     * @return array
     */
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
                        'country_code' => (string) $address->getCountryId(),
                        'country' => (string) $address->getCountry(),
                        'city' => (string) $address->getCity(),
                        'region' => (string) $address->getRegion(),
                        'region_code' => (string) $address->getRegionCode(),
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->_logger->error('Delivery Countdown Customer Location Error: ' . $e->getMessage());
        }

        return [];
    }
}
