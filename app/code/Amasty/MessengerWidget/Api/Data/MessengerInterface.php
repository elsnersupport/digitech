<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Api\Data;

interface MessengerInterface
{
    public const MESSENGER_ID = 'messenger_id';
    public const IS_ACTIVE = 'is_active';
    public const CODE = 'code';
    public const CUSTOM_NAME = 'custom_name';
    public const LINK = 'link';
    public const COMMENT = 'comment';
    public const TOOLTIP = 'tooltip';
    public const SORT_ORDER = 'sort_order';
    public const STORE_IDS = 'store_ids';
    public const ICON = 'icon';

    /**
     * @return int
     */
    public function getMessengerId(): int;

    /**
     * @param int $messengerId
     * @return void
     */
    public function setMessengerId(int $messengerId): void;

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @param bool $isActive
     * @return void
     */
    public function setIsActive(bool $isActive): void;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param string $code
     * @return void
     */
    public function setCode(string $code): void;

    /**
     * @return string
     */
    public function getCustomName(): string;

    /**
     * @param string $customName
     * @return void
     */
    public function setCustomName(string $customName): void;

    /**
     * @return string
     */
    public function getLink(): string;

    /**
     * @param string $link
     * @return void
     */
    public function setLink(string $link): void;

    /**
     * @return string
     */
    public function getComment(): string;

    /**
     * @param string $comment
     * @return void
     */
    public function setComment(string $comment): void;

    /**
     * @return string
     */
    public function getTooltip(): string;

    /**
     * @param string $tooltip
     * @return void
     */
    public function setTooltip(string $tooltip): void;

    /**
     * @return int
     */
    public function getSortOrder(): int;

    /**
     * @param int $sortOrder
     * @return void
     */
    public function setSortOrder(int $sortOrder): void;

    /**
     * @return int[]
     */
    public function getStoreIds(): array;

    /**
     * @param int[] $storeIds
     * @return void
     */
    public function setStoreIds(array $storeIds): void;

    /**
     * @return string
     */
    public function getIcon(): string;

    /**
     * @param string $icon
     */
    public function setIcon(string $icon): void;
}
