<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\ResourceModel\Messenger\Grid\Collection\FilterAndSortingApplier;

use Amasty\MessengerWidget\Model\ResourceModel\Collection\FilterAndSortingApplierInterface;
use Amasty\MessengerWidget\Api\Data\MessengerInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class MessengerName implements FilterAndSortingApplierInterface
{
    /**
     * @var array
     */
    private $applicableFields = [
        'name'
    ];

    public function __construct(
        array $applicableFields = []
    ) {
        $this->applicableFields = array_merge($this->applicableFields, $applicableFields);
    }

    /**
     * @param AbstractCollection $collection
     * @param string $field
     * @param array|string
     * ng|null $condition
     */
    public function applyFilter(AbstractCollection $collection, string $field, $condition = null): void
    {
        $collection->addFieldToFilter($field, $condition);
    }
    
    /**
     * @param AbstractCollection $collection
     * @param string $field
     * @param string $direction
     */
    public function applySorting(AbstractCollection $collection, string $field, string $direction): void
    {
        $collection->getSelect()->order([MessengerInterface::CODE . ' ' . $direction]);
    }

    /**
     * @param string $field
     * @return bool
     */
    public function canApply(string $field): bool
    {
        return in_array($field, $this->applicableFields);
    }
}
