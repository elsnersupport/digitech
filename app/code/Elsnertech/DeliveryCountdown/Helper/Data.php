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
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'delivery_countdown/general/enabled';
    const XML_PATH_CUTOFF_TIME = 'delivery_countdown/general/cutoff_time';
    const XML_PATH_SAME_DAY_ENABLED = 'delivery_countdown/general/same_day_enabled';
    const XML_PATH_NEXT_DAY_ENABLED = 'delivery_countdown/general/next_day_enabled';
    const XML_PATH_EXCLUDE_WEEKENDS = 'delivery_countdown/general/exclude_weekends';
    const XML_PATH_SAME_DAY_MESSAGE = 'delivery_countdown/general/same_day_message';
    const XML_PATH_NEXT_DAY_MESSAGE = 'delivery_countdown/general/next_day_message';
    const XML_PATH_COUNTDOWN_TEXT = 'delivery_countdown/general/countdown_text';
    const XML_PATH_NON_WORKING_DAYS = 'delivery_countdown/general/non_working_days';
    
    const XML_PATH_ENABLE_LOCATION_CHECK = 'delivery_countdown/location/enable_location_check';
    const XML_PATH_ALLOWED_COUNTRIES = 'delivery_countdown/location/allowed_countries';
    const XML_PATH_ALLOWED_REGIONS = 'delivery_countdown/location/allowed_regions';
    const XML_PATH_USE_IP_DETECTION = 'delivery_countdown/location/use_ip_detection';
    const XML_PATH_GEOLOCATION_API_KEY = 'delivery_countdown/location/geolocation_api_key';
    const XML_PATH_FALLBACK_TO_SESSION = 'delivery_countdown/location/fallback_to_session';
    const XML_PATH_PREVIEW_IPS = 'delivery_countdown/location/preview_ips';

    /**
     * Check if delivery countdown is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get cutoff time
     *
     * @return string
     */
    public function getCutoffTime()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CUTOFF_TIME,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if same day delivery is enabled
     *
     * @return bool
     */
    public function isSameDayEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SAME_DAY_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if next day delivery is enabled
     *
     * @return bool
     */
    public function isNextDayEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_NEXT_DAY_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if weekends should be excluded
     *
     * @return bool
     */
    public function isExcludeWeekends()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EXCLUDE_WEEKENDS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get same day delivery message
     *
     * @return string
     */
    public function getSameDayMessage()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SAME_DAY_MESSAGE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get next day delivery message
     *
     * @return string
     */
    public function getNextDayMessage()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_NEXT_DAY_MESSAGE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get countdown text
     *
     * @return string
     */
    public function getCountdownText()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COUNTDOWN_TEXT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if location-based check is enabled
     *
     * @return bool
     */
    public function isLocationCheckEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_LOCATION_CHECK,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get allowed countries
     *
     * @return array
     */
    public function getAllowedCountries()
    {
        $countries = $this->scopeConfig->getValue(
            self::XML_PATH_ALLOWED_COUNTRIES,
            ScopeInterface::SCOPE_STORE
        );
        return $countries ? explode(',', $countries) : [];
    }

    /**
     * Get allowed regions/cities
     *
     * @return array
     */
    public function getAllowedRegions()
    {
        $regions = $this->scopeConfig->getValue(
            self::XML_PATH_ALLOWED_REGIONS,
            ScopeInterface::SCOPE_STORE
        );
        if (empty($regions)) {
            return [];
        }
        return array_map('trim', explode("\n", $regions));
    }

    /**
     * Check if IP detection is enabled
     *
     * @return bool
     */
    public function isIpDetectionEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_IP_DETECTION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get geolocation API key
     *
     * @return string
     */
    public function getGeolocationApiKey()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GEOLOCATION_API_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if fallback to customer session is enabled
     *
     * @return bool
     */
    public function isFallbackToSessionEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FALLBACK_TO_SESSION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the days of the week on which no delivery happens
     *
     * Values follow PHP's date('w') numbering (0 = Sunday ... 6 = Saturday) so the
     * same list can be handed to the browser and compared against Date::getDay().
     * Returns an empty array when the exclusion is switched off, or when every day
     * has been selected, since treating every day as non-working would leave the
     * delivery date unresolvable.
     *
     * @return int[]
     */
    public function getNonWorkingDays()
    {
        if (!$this->isExcludeWeekends()) {
            return [];
        }

        $configured = $this->scopeConfig->getValue(
            self::XML_PATH_NON_WORKING_DAYS,
            ScopeInterface::SCOPE_STORE
        );

        if ($configured === null || $configured === '') {
            return [];
        }

        $days = array_map('intval', explode(',', (string) $configured));
        $days = array_values(array_unique(array_filter($days, function ($day) {
            return $day >= 0 && $day <= 6;
        })));

        return count($days) >= 7 ? [] : $days;
    }
    /**
     * Get the addresses that always see the countdown, whatever their location
     *
     * Intended for the people building and supporting the site, so they can check
     * a region restricted widget from their own desk without opening it up to real
     * shoppers nearby. Accepts single addresses and IPv4 CIDR ranges, separated by
     * new lines or commas.
     *
     * @return string[]
     */
    public function getPreviewIps()
    {
        $configured = $this->scopeConfig->getValue(
            self::XML_PATH_PREVIEW_IPS,
            ScopeInterface::SCOPE_STORE
        );

        if (empty($configured)) {
            return [];
        }

        $entries = preg_split('/[\s,]+/', trim((string) $configured));

        return array_values(array_filter(array_map('trim', $entries ?: []), function ($entry) {
            return $entry !== '';
        }));
    }
}
