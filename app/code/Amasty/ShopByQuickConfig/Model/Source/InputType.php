<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Swatches\Model\Swatch;

class InputType implements OptionSourceInterface
{
    public const CUSTOM_TYPE = 'am_custom';

    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Amasty Custom Filter'), 'value' => self::CUSTOM_TYPE],
            ['label' => __('Yes/No'), 'value' => 'boolean'],
            ['label' => __('Dropdown'), 'value' => 'select'],
            ['label' => __('Visual Swatch'), 'value' => Swatch::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT],
            ['label' => __('Text Swatch'), 'value' => Swatch::SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT],
            ['label' => __('Multiple Select'), 'value' => 'multiselect'],
            ['label' => __('Price'), 'value' => 'price']
        ];
    }
}
