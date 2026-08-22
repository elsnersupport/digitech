<?php
/**
 * Elsnertech_DeliveryCountdown
 * 
 * @category    Elsnertech
 * @package     Elsnertech_DeliveryCountdown
 * @author      Elsnertech
 * @copyright   Copyright (c) 2025 Elsnertech
 */

namespace Elsnertech\DeliveryCountdown\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Elsnertech\DeliveryCountdown\Helper\Data as DeliveryHelper;
use Elsnertech\DeliveryCountdown\Helper\Location as LocationHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class DeliveryCountdown implements ArgumentInterface
{
    /**
     * @var DeliveryHelper
     */
    protected $deliveryHelper;

    /**
     * @var LocationHelper
     */
    protected $locationHelper;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @param DeliveryHelper $deliveryHelper
     * @param LocationHelper $locationHelper
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        DeliveryHelper $deliveryHelper,
        LocationHelper $locationHelper,
        TimezoneInterface $timezone
    ) {
        $this->deliveryHelper = $deliveryHelper;
        $this->locationHelper = $locationHelper;
        $this->timezone = $timezone;
    }

    /**
     * Check if delivery countdown is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        // Check if module is enabled
        if (!$this->deliveryHelper->isEnabled()) {
            return false;
        }

        // Check if visitor is from allowed location
        if (!$this->locationHelper->isAllowedLocation()) {
            return false;
        }

        return true;
    }

    /**
     * Get delivery information
     *
     * @return array
     */
    public function getDeliveryInfo()
    {
        if (!$this->isEnabled()) {
            return [];
        }

        $currentTime = $this->timezone->date();
        $cutoffTime = $this->deliveryHelper->getCutoffTime();

        list($cutoffHour, $cutoffMinute) = explode(':', $cutoffTime);

        $cutoffDateTime = clone $currentTime;
        $cutoffDateTime->setTime((int) $cutoffHour, (int) $cutoffMinute, 0);

        $isSameDayPossible = $this->deliveryHelper->isSameDayEnabled() &&
            $currentTime < $cutoffDateTime;

        $deliveryDate = $this->calculateDeliveryDate($currentTime, $cutoffDateTime, $isSameDayPossible);
        $originalCutoffDateTime = clone $cutoffDateTime;
        $timeRemaining = $cutoffDateTime->getTimestamp() - $currentTime->getTimestamp();

        if ($timeRemaining < 0) {
            $weekoffdays = ['Sun'];
            $cutoffDateTime->modify('+1 days');
            if ($this->deliveryHelper->isExcludeWeekends() && in_array($cutoffDateTime->format('D'), $weekoffdays)) {
                while (in_array($cutoffDateTime->format('D'), $weekoffdays)) {
                    $cutoffDateTime->modify('+1 days');
                }
            }
            $timeRemaining = $cutoffDateTime->getTimestamp() - $currentTime->getTimestamp();
        }

        $hours = floor($timeRemaining / 3600);
        $minutes = floor(($timeRemaining % 3600) / 60);
        $seconds = $timeRemaining % 60;

        $message = $isSameDayPossible ?
            $this->deliveryHelper->getSameDayMessage() :
            $this->deliveryHelper->getNextDayMessage();

        $formattedDate = $deliveryDate->format('d M');
        $message = str_replace('{date}', $formattedDate, $message);
        $message = str_replace('{time}', $cutoffTime, $message);

        $countdownText = $this->deliveryHelper->getCountdownText();
        $countdownText = str_replace('{hours}', $hours, $countdownText);
        $countdownText = str_replace('{mins}', $minutes, $countdownText);

        return [
            'enabled' => true,
            'is_same_day' => $isSameDayPossible,
            'delivery_date' => $deliveryDate->format('Y-m-d'),
            'delivery_date_formatted' => $formattedDate,
            'cutoff_timestamp' => $originalCutoffDateTime->getTimestamp(),
            'current_timestamp' => $currentTime->getTimestamp(),
            'current_time_formatted' => $currentTime->format('Y-m-d'),
            'hours_remaining' => $hours,
            'minutes_remaining' => $minutes,
            'seconds_remaining' => $seconds,
            'delivery_message' => $message,
            'countdown_text' => $countdownText,
        ];
    }

    /**
     * Calculate delivery date
     *
     * @param \DateTime $currentTime
     * @param \DateTime $cutoffDateTime
     * @param bool $isSameDayPossible
     * @return \DateTime
     */
    protected function calculateDeliveryDate($currentTime, $cutoffDateTime, $isSameDayPossible)
    {
        $deliveryDate = clone $currentTime;

        // Then add delivery days
        if (!$isSameDayPossible) {
            $daysToAdd = 1;
            $deliveryDate->modify("+{$daysToAdd} days");
        }
        
        $weekoffdays = ['Sun'];

        // Exclude weekends if configured
        if ($this->deliveryHelper->isExcludeWeekends()) {
            while (in_array($deliveryDate->format('D'), $weekoffdays)) {
                $deliveryDate->modify('+1 days');
            }
        }


        return $deliveryDate;
    }

    /**
     * Get configuration as JSON
     *
     * @return string
     */
    public function getConfigJson()
    {
        return json_encode($this->getDeliveryInfo());
    }
}