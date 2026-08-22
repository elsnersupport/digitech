<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

interface FilterAndSortingApplierInterface
{
    /**
     * Apply filters for given collection
     *
     * @param AbstractCollection $collection
     * @param string $field
     * @param array|string|null $condition
     * @return void
     */
    public function applyFilter(AbstractCollection $collection, string $field, $condition = null): void;

    /**
     * Apply sorting for given collection
     *
     * @param AbstractCollection $collection
     * @param string $field
     * @param string $direction
     * @return void
     */
    public function applySorting(AbstractCollection $collection, string $field, string $direction): void;

    /**
     * Check if possible to apply field filter
     *
     * @param string $field
     * @return bool
     */
    public function canApply(string $field): bool;
}
