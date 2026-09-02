<?php
/**
 * Elsnertech_DeliveryCountdown
 *
 * @category    Elsnertech
 * @package     Elsnertech_DeliveryCountdown
 * @author      Elsnertech
 * @copyright   Copyright (c) 2025 Elsnertech
 */

namespace Elsnertech\DeliveryCountdown\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Days of the week, numbered the same way as PHP's date('w') and JavaScript's
 * Date::getDay() so the same values can drive both the server and the browser.
 */
class DaysOfWeek implements OptionSourceInterface
{
    public const SUNDAY = 0;
    public const SATURDAY = 6;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::SUNDAY, 'label' => __('Sunday')],
            ['value' => 1, 'label' => __('Monday')],
            ['value' => 2, 'label' => __('Tuesday')],
            ['value' => 3, 'label' => __('Wednesday')],
            ['value' => 4, 'label' => __('Thursday')],
            ['value' => 5, 'label' => __('Friday')],
            ['value' => self::SATURDAY, 'label' => __('Saturday')],
        ];
    }
}
