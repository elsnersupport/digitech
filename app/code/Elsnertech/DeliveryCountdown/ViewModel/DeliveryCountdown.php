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
use Psr\Log\LoggerInterface;

class DeliveryCountdown implements ArgumentInterface
{
    /**
     * Format used for the {date} placeholder, mirrored by the browser so both
     * halves of the widget render the same string.
     */
    public const DATE_FORMAT = 'd M';

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DeliveryHelper $deliveryHelper
     * @param LocationHelper $locationHelper
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeliveryHelper $deliveryHelper,
        LocationHelper $locationHelper,
        TimezoneInterface $timezone,
        LoggerInterface $logger
    ) {
        $this->deliveryHelper = $deliveryHelper;
        $this->locationHelper = $locationHelper;
        $this->timezone = $timezone;
        $this->logger = $logger;
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

        // A malformed cutoff time would produce a meaningless countdown, so the
        // widget stays hidden rather than showing a wrong promise to the shopper.
        if ($this->getCutoffParts() === null) {
            $this->logger->warning(
                'Delivery Countdown: cutoff time is missing or not in HH:MM format, widget hidden.'
            );
            return false;
        }

        // Check if visitor is from allowed location
        if (!$this->locationHelper->isAllowedLocation()) {
            return false;
        }

        return true;
    }

    /**
     * Get delivery information as calculated on the server
     *
     * The storefront renders from getJsConfig() instead, because this snapshot is
     * only correct at the moment the page is built and product pages are held in
     * the full page cache. This method is kept as the canonical description of the
     * rules and is what the unit tests assert against.
     *
     * @return array
     */
    public function getDeliveryInfo()
    {
        if (!$this->isEnabled()) {
            return [];
        }

        list($cutoffHour, $cutoffMinute) = $this->getCutoffParts();

        $currentTime = $this->timezone->date();
        $cutoffDateTime = clone $currentTime;
        $cutoffDateTime->setTime($cutoffHour, $cutoffMinute, 0);

        $todayIsWorkingDay = $this->isWorkingDay($currentTime);
        $beforeCutoff = $currentTime < $cutoffDateTime;

        // The weekend rule has to take part in this decision, not just in the
        // delivery date below. Without the working-day test a Sunday morning
        // visit still qualified as same day, so the widget promised delivery
        // today while its own delivery date had already moved to Monday.
        $isSameDayPossible = $this->deliveryHelper->isSameDayEnabled()
            && $todayIsWorkingDay
            && $beforeCutoff;

        $deliveryDate = $isSameDayPossible
            ? clone $currentTime
            : $this->getNextWorkingDay($currentTime);

        // Orders placed before today's cutoff still count towards today; once it
        // has passed, or today is not a working day, the next chance to order is
        // the cutoff on the next working day.
        if ($todayIsWorkingDay && $beforeCutoff) {
            $orderDeadline = clone $cutoffDateTime;
        } else {
            $orderDeadline = $this->getNextWorkingDay($currentTime);
            $orderDeadline->setTime($cutoffHour, $cutoffMinute, 0);
        }

        $timeRemaining = max(0, $orderDeadline->getTimestamp() - $currentTime->getTimestamp());

        $message = $isSameDayPossible
            ? $this->deliveryHelper->getSameDayMessage()
            : $this->deliveryHelper->getNextDayMessage();
        $message = $this->renderMessage($message, $deliveryDate, $currentTime);

        return [
            'enabled' => true,
            'is_same_day' => $isSameDayPossible,
            'delivery_date' => $deliveryDate->format('Y-m-d'),
            'delivery_date_formatted' => $deliveryDate->format(self::DATE_FORMAT),
            'cutoff_timestamp' => $orderDeadline->getTimestamp(),
            'current_timestamp' => $currentTime->getTimestamp(),
            'current_time_formatted' => $currentTime->format('Y-m-d'),
            'hours_remaining' => (int) floor($timeRemaining / 3600),
            'minutes_remaining' => (int) floor(($timeRemaining % 3600) / 60),
            'seconds_remaining' => $timeRemaining % 60,
            'delivery_message' => $message,
        ];
    }

    /**
     * Get the rules the browser needs to work out the delivery state for itself
     *
     * Only configuration is handed over, never a decision or a timestamp taken at
     * render time. That keeps the markup valid for as long as the full page cache
     * chooses to hold it: a page built on Saturday still resolves correctly when
     * it is served on Monday.
     *
     * @return array
     */
    public function getJsConfig()
    {
        if (!$this->isEnabled()) {
            return ['enabled' => false];
        }

        list($cutoffHour, $cutoffMinute) = $this->getCutoffParts();

        return [
            'enabled' => true,
            'timezone' => $this->timezone->getConfigTimezone(),
            'cutoffHour' => $cutoffHour,
            'cutoffMinute' => $cutoffMinute,
            'cutoffLabel' => (string) $this->deliveryHelper->getCutoffTime(),
            'nonWorkingDays' => $this->deliveryHelper->getNonWorkingDays(),
            'sameDayEnabled' => $this->deliveryHelper->isSameDayEnabled(),
            'sameDayMessage' => (string) $this->deliveryHelper->getSameDayMessage(),
            'nextDayMessage' => (string) $this->deliveryHelper->getNextDayMessage(),
            'dayLabels' => $this->getDayLabels(),
        ];
    }

    /**
     * Get configuration as JSON
     *
     * @return string
     */
    public function getConfigJson()
    {
        return json_encode($this->getJsConfig());
    }

    /**
     * Substitute the placeholders a message may contain
     *
     * @param string|null $message
     * @param \DateTime $deliveryDate
     * @param \DateTime $currentTime
     * @return string
     */
    private function renderMessage($message, \DateTime $deliveryDate, \DateTime $currentTime)
    {
        return str_replace(
            ['{day}', '{date}', '{time}'],
            [
                $this->getDayLabel($deliveryDate, $currentTime),
                $deliveryDate->format(self::DATE_FORMAT),
                (string) $this->deliveryHelper->getCutoffTime(),
            ],
            (string) $message
        );
    }

    /**
     * Word describing how far off the delivery is, for the {day} placeholder
     *
     * This restores the wording the widget used before the delivery date became
     * configurable: "Tomorrow" when the parcel arrives the very next day, and the
     * plain preposition once a non-working day has pushed it further out.
     *
     * @param \DateTime $deliveryDate
     * @param \DateTime $currentTime
     * @return string
     */
    private function getDayLabel(\DateTime $deliveryDate, \DateTime $currentTime)
    {
        $today = (clone $currentTime)->setTime(0, 0, 0);
        $target = (clone $deliveryDate)->setTime(0, 0, 0);
        $offset = (int) $today->diff($target)->days;

        $labels = $this->getDayLabels();

        if ($offset <= 0) {
            return $labels['today'];
        }

        return $offset === 1 ? $labels['tomorrow'] : $labels['later'];
    }

    /**
     * Translated words the {day} placeholder can resolve to
     *
     * Handed to the browser as well, so the client side never has to hardcode
     * English of its own.
     *
     * @return string[]
     */
    private function getDayLabels()
    {
        return [
            'today' => (string) __('Today'),
            'tomorrow' => (string) __('Tomorrow'),
            'later' => (string) __('on'),
        ];
    }

    /**
     * Split the configured cutoff into hour and minute
     *
     * @return int[]|null Null when the value is missing or not HH:MM
     */
    private function getCutoffParts()
    {
        $cutoffTime = trim((string) $this->deliveryHelper->getCutoffTime());

        if (!preg_match('/^([01]?\d|2[0-3]):([0-5]?\d)$/', $cutoffTime, $matches)) {
            return null;
        }

        return [(int) $matches[1], (int) $matches[2]];
    }

    /**
     * Check whether deliveries run on the given day
     *
     * @param \DateTime $date
     * @return bool
     */
    private function isWorkingDay(\DateTime $date)
    {
        return !in_array((int) $date->format('w'), $this->deliveryHelper->getNonWorkingDays(), true);
    }

    /**
     * Get the first working day strictly after the given date
     *
     * @param \DateTime $date
     * @return \DateTime
     */
    private function getNextWorkingDay(\DateTime $date)
    {
        $nextDay = clone $date;

        do {
            $nextDay->modify('+1 day');
        } while (!$this->isWorkingDay($nextDay));

        return $nextDay;
    }
}
