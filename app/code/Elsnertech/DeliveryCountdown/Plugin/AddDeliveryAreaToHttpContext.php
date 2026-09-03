<?php
/**
 * Elsnertech_DeliveryCountdown
 *
 * @category    Elsnertech
 * @package     Elsnertech_DeliveryCountdown
 * @author      Elsnertech
 * @copyright   Copyright (c) 2025 Elsnertech
 */

namespace Elsnertech\DeliveryCountdown\Plugin;

use Elsnertech\DeliveryCountdown\Helper\Location;
use Magento\Framework\App\Http\Context as HttpContext;

/**
 * Make the page cache aware of whether the visitor is inside the delivery area
 *
 * The countdown is shown to some visitors and withheld from others, but product
 * pages are held in the full page cache and the cache key knows nothing about
 * where the request came from. Without this, whichever visitor happens to warm a
 * page decides what everyone after them sees: one shopper in Dubai would leave
 * the countdown cached for the whole country.
 *
 * The value is deliberately a yes or no rather than the emirate itself, so the
 * cache splits into two variants instead of one per city.
 */
class AddDeliveryAreaToHttpContext
{
    public const CONTEXT_DELIVERY_AREA = 'elsnertech_delivery_area';

    /**
     * @var Location
     */
    private $locationHelper;

    /**
     * @param Location $locationHelper
     */
    public function __construct(Location $locationHelper)
    {
        $this->locationHelper = $locationHelper;
    }

    /**
     * Add the delivery area to the cache vary string before it is generated
     *
     * @param HttpContext $subject
     * @return null
     */
    public function beforeGetVaryString(HttpContext $subject)
    {
        $subject->setValue(
            self::CONTEXT_DELIVERY_AREA,
            $this->locationHelper->isAllowedLocation() ? 1 : 0,
            0
        );

        return null;
    }
}
