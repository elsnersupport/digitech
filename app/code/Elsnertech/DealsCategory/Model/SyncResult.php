<?php
/**
 * Elsnertech_DealsCategory
 *
 * @category    Elsnertech
 * @package     Elsnertech_DealsCategory
 * @author      Elsnertech
 * @copyright   Copyright (c) 2026 Elsnertech
 */
declare(strict_types=1);

namespace Elsnertech\DealsCategory\Model;

/**
 * Outcome of one deals category sync.
 */
class SyncResult
{
    /**
     * @var int
     */
    private $categoryId;

    /**
     * @var int[]
     */
    private $dealProductIds;

    /**
     * @var int[]
     */
    private $addedProductIds;

    /**
     * @var int[]
     */
    private $removedProductIds;

    /**
     * @var bool
     */
    private $saved;

    /**
     * @param int $categoryId
     * @param int[] $dealProductIds
     * @param int[] $addedProductIds
     * @param int[] $removedProductIds
     * @param bool $saved
     */
    public function __construct(
        int $categoryId,
        array $dealProductIds,
        array $addedProductIds,
        array $removedProductIds,
        bool $saved
    ) {
        $this->categoryId = $categoryId;
        $this->dealProductIds = $dealProductIds;
        $this->addedProductIds = $addedProductIds;
        $this->removedProductIds = $removedProductIds;
        $this->saved = $saved;
    }

    /**
     * The deals category that was synced.
     *
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    /**
     * Every product that qualifies as a deal right now.
     *
     * @return int[]
     */
    public function getDealProductIds(): array
    {
        return $this->dealProductIds;
    }

    /**
     * Deals that were not in the category yet.
     *
     * @return int[]
     */
    public function getAddedProductIds(): array
    {
        return $this->addedProductIds;
    }

    /**
     * Products in the category that are no longer a deal.
     *
     * @return int[]
     */
    public function getRemovedProductIds(): array
    {
        return $this->removedProductIds;
    }

    /**
     * Whether any product has to be added or removed.
     *
     * @return bool
     */
    public function hasChanges(): bool
    {
        return $this->addedProductIds !== [] || $this->removedProductIds !== [];
    }

    /**
     * False when nothing changed or when it was a dry run.
     *
     * @return bool
     */
    public function isSaved(): bool
    {
        return $this->saved;
    }
}
