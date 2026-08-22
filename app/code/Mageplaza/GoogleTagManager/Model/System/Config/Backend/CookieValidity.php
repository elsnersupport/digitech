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

namespace Mageplaza\GoogleTagManager\Model\System\Config\Backend;

use Magento\Framework\App\Config\Value;

/**
 * Class CookieValidity
 * @package Mageplaza\GoogleTagManager\Model\System\Config\Backend
 */
class CookieValidity extends Value
{
    /**
     * @return Value
     */
    public function beforeSave()
    {
        $value = (int) $this->getValue();
        if (!$value || $value === 0 || $value > 365) {
            $this->setValue('365');
        }

        return parent::beforeSave();
    }
}
