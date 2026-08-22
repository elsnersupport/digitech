<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Ui\Component\Listing\Column;

class Store extends \Magento\Store\Ui\Component\Listing\Column\Store
{
    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item): string
    {
        if (!empty($item[$this->storeKey])) {
            $item[$this->storeKey] = explode(',', $item[$this->storeKey]);
        }

        return parent::prepareItem($item);
    }
}
