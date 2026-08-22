<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Model\ConfigFilter;

class FilterCodeRegistry
{
    /**
     * @var array
     */
    private $customFilterCodes;

    /**
     * @param string[] $customFilterCodes
     */
    public function __construct(array $customFilterCodes = [])
    {
        $this->customFilterCodes = $customFilterCodes;
    }

    /**
     * @return string[]
     */
    public function getCustomFilterCodes(): array
    {
        return $this->customFilterCodes;
    }
}
