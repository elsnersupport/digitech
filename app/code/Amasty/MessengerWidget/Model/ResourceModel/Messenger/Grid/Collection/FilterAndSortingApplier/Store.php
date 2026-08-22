<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\ResourceModel\Messenger\Grid\Collection\FilterAndSortingApplier;

use Amasty\MessengerWidget\Model\ResourceModel\Collection\FilterAndSortingApplierInterface;
use Amasty\MessengerWidget\Model\ResourceModel\Messenger;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Store implements FilterAndSortingApplierInterface
{
    public const ALIAS = 'store';

    /**
     * @var array
     */
    private $applicableFields = [
        'store_id'
    ];

    public function __construct(
        array $applicableFields = []
    ) {
        $this->applicableFields = array_merge($this->applicableFields, $applicableFields);
    }

    /**
     * @param AbstractCollection $collection
     * @param string $field
     * @param array|string|null $condition
     */
    public function applyFilter(AbstractCollection $collection, string $field, $condition = null): void
    {
        $this->joinTable($collection);
        $collection
            ->addFilter($field, $condition, 'public')
            ->addFilterToMap($field, self::ALIAS . '.' . $field);
    }

    /**
     * @param AbstractCollection $collection
     * @param string $field
     * @param string $direction
     */
    public function applySorting(AbstractCollection $collection, string $field, string $direction): void
    {
        $this->joinTable($collection);
        $collection->getSelect()->order(self::ALIAS . '.' . $field . ' ' . $direction);
    }

    /**
     * @param string $field
     * @return bool
     */
    public function canApply(string $field): bool
    {
        return in_array($field, $this->applicableFields);
    }

    /**
     * @param AbstractCollection $collection
     * @return void
     */
    private function joinTable(AbstractCollection $collection): void
    {
        $fromPart = (array)$collection->getSelect()->getPart(Select::FROM);

        if (!array_key_exists(self::ALIAS, $fromPart)) {
            $collection->getSelect()->joinLeft(
                [self::ALIAS => $collection->getTable(Messenger::STORES_TABLE)],
                'main_table.messenger_id = ' . self::ALIAS . '.messenger_id',
                []
            );
        }
    }
}
