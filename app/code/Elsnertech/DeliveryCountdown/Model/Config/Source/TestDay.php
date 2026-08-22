<?php
namespace Elsnertech\DeliveryCountdown\Model\Config\Source;

class TestDay implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'current', 'label' => __('Current Day')],
            ['value' => 'monday', 'label' => __('Monday')],
            ['value' => 'friday', 'label' => __('Friday')],
            ['value' => 'saturday', 'label' => __('Saturday')],
            ['value' => 'sunday', 'label' => __('Sunday')]
        ];
    }
}