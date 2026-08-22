<?php

namespace Infibeam\Ccavenue\Model\Source;

class CurrencyList implements \Magento\Framework\Option\ArrayInterface {

    public function toOptionArray() {
        return [
            [
                'value' => "AED",
                'label' => __('United Arab Emirates Dirham')
            ],
            [
                'value' => "QAR",
                'label' => __('Qatar Rial')
            ],
            [
                'value' => "SAR",
                'label' => __('Saudi Riyal')
            ],
            [
                'value' => "KWD",
                'label' => __('Kuwaiti Dinar')
            ],
            [
                'value' => "BHD",
                'label' => __('Bahraini Dinar')
            ],
            [
                'value' => "OMR",
                'label' => __('Omani Rial')
            ],
            [
                'value' => "USD",
                'label' => __('US Dollar')
            ],
            [
                'value' => "GBP",
                'label' => __('British Pound')
            ],
            [
                'value' => "EUR",
                'label' => __('Euro')
            ],
            [
                'value' => "AUD",
                'label' => __('Australian Dollar')
            ],
            [
                'value' => "CAD",
                'label' => __('Canadian Dollar')
            ],
            [
                'value' => "JPY",
                'label' => __('Japanese Yen')
            ],
            [
                'value' => "NZD",
                'label' => __('New Zealand Dollar')
            ],
            [
                'value' => "CHF",
                'label' => __('Swiss Franc')
            ],
            [
                'value' => "HKD",
                'label' => __('Hong Kong Dollar')
            ],
            [
                'value' => "SGD",
                'label' => __('Singapore Dollar')
            ],
            [
                'value' => "SEK",
                'label' => __('Swedish Krona')
            ],
            [
                'value' => "DKK",
                'label' => __('Danish Krone')
            ],
            [
                'value' => "PLN",
                'label' => __('Polish Zloty')
            ],
            [
                'value' => "NOK",
                'label' => __('Norwegian Krone')
            ],
            [
                'value' => "HUF",
                'label' => __('Hungarian Forint')
            ],
            [
                'value' => "CZK",
                'label' => __('Czech Republic Koruna')
            ],
            [
                'value' => "ILS",
                'label' => __('Israeli New Sheqel')
            ],
            [
                'value' => "MXN",
                'label' => __('Mexican Peso')
            ],
            [
                'value' => "MYR",
                'label' => __('Malaysian Ringgit')
            ],
            [
                'value' => "BRL",
                'label' => __('Brazilian Real')
            ],
            [
                'value' => "PHP",
                'label' => __('Philippine Peso')
            ],
            [
                'value' => "TWD",
                'label' => __('New Taiwan Dollar')
            ],
            [
                'value' => "THB",
                'label' => __('Thai Baht')
            ],
        ];
    }

}
