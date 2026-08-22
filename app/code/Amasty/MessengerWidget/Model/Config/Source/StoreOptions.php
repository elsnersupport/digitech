<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\Store;
use Magento\Store\Ui\Component\Listing\Column\Store\Options;

class StoreOptions implements OptionSourceInterface
{
    /**
     * All Store Views value
     */
    private const ALL_STORE_VIEWS = Store::DEFAULT_STORE_ID;

    /**
     * @var Options
     */
    private $storeOptions;

    public function __construct(Options $storeOptions)
    {
        $this->storeOptions = $storeOptions;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [
            ['value' => self::ALL_STORE_VIEWS, 'label' => __('All Store Views')]
        ];

        $options = array_merge($options, $this->storeOptions->toOptionArray());

        return $options;
    }
}
