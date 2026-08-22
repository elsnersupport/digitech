<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

interface DataFillerInterface
{
    /**
     * Attach related collection data
     *
     * @param AbstractCollection $collection
     * @return void
     */
    public function attachData(AbstractCollection $collection): void;
}
