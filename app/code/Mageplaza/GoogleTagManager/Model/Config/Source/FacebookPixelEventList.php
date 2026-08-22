<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_GoogleTagManager
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GoogleTagManager\Model\Config\Source;

/**
 * Class FacebookPixelEventList
 * @package Mageplaza\GoogleTagManager\Model\Config\Source
 */
class FacebookPixelEventList extends AbstractSource
{
    const SELECT           = 0;
    const SEARCH           = 1;
    const WISHLIST         = 2;
    const REGISTRATION     = 3;
    const PAYMENT          = 4;
    const ADD_TO_CART      = 5;
    const BEGIN_CHECKOUT   = 6;
    const PURCHASE         = 7;
    const VIEW_CONTENT     = 8;

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::SELECT           => __('-- Please select --'),
            self::SEARCH           => __('Search Result Page'),
            self::WISHLIST         => __('Add to Wishlist'),
            self::REGISTRATION     => __('Customer Registration'),
            self::PAYMENT          => __('Add Payment'),
            self::ADD_TO_CART      => __('Add to Cart'),
            self::BEGIN_CHECKOUT   => __('Begins Checkout'),
            self::PURCHASE         => __('Purchase'),
            self::VIEW_CONTENT     => __('View Content')
        ];
    }
}
