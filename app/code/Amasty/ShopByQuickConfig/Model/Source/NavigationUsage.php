<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Model\Source;

use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\LayeredNavigation\Model\Attribute\Source\FilterableOptions;

class NavigationUsage implements OptionSourceInterface
{
    /**
     * @var FilterableOptions
     */
    private $filterableOptions;

    /**
     * @var Yesno
     */
    private $yesNo;

    public function __construct(FilterableOptions $filterableOptions, Yesno $yesNo)
    {
        $this->filterableOptions = $filterableOptions;
        $this->yesNo = $yesNo;
    }

    public function toOptionArray(): array
    {
        return [
            '0' => $this->filterableOptions->toOptionArray(),
            '1' => $this->yesNo->toOptionArray()
        ];
    }
}
