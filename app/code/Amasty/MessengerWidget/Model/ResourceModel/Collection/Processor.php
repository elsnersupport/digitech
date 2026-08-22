<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Processor
{
    /**
     * @var FilterAndSortingApplierInterface[]
     */
    private $filterAndSortingAppliers = [];

    /**
     * @var DataFillerInterface[]
     */
    private $dataFillers = [];

    /**
     * @param FilterAndSortingApplierInterface[] $filterAndSortingAppliers
     * @param DataFillerInterface[] $dataFillers
     */
    public function __construct(
        array $filterAndSortingAppliers = [],
        array $dataFillers = []
    ) {
        $this->filterAndSortingAppliers = $filterAndSortingAppliers;
        $this->dataFillers = $dataFillers;
    }

    /**
     * @param AbstractCollection $collection
     * @param string $field
     * @param string|array|null $condition
     * @return bool
     */
    public function applyFilter(AbstractCollection $collection, string $field, $condition = null): bool
    {
        foreach ($this->filterAndSortingAppliers as $applier) {
            if ($applier instanceof FilterAndSortingApplierInterface && $applier->canApply($field)) {
                $applier->applyFilter($collection, $field, $condition);

                return true;
            }
        }

        return false;
    }

    /**
     * @param AbstractCollection $collection
     * @param string $field
     * @param string $direction
     * @return bool
     */
    public function applySorting(AbstractCollection $collection, string $field, string $direction): bool
    {
        foreach ($this->filterAndSortingAppliers as $applier) {
            if ($applier instanceof FilterAndSortingApplierInterface && $applier->canApply($field)) {
                $applier->applySorting($collection, $field, $direction);

                return true;
            }
        }

        return false;
    }

    /**
     * @param AbstractCollection $collection
     * @return void
     */
    public function attachData(AbstractCollection $collection): void
    {
        foreach ($this->dataFillers as $dataFiller) {
            if ($dataFiller instanceof DataFillerInterface) {
                $dataFiller->attachData($collection);
            }
        }
    }
}
