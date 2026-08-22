<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model;

use Amasty\MessengerWidget\Api\Data\MessengerInterface;

class Messenger extends \Magento\Framework\Model\AbstractModel implements MessengerInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'amasty_messenget_widget_messenger';

    /**
     * Init Model
     */
    public function _construct()
    {
        $this->_init(\Amasty\MessengerWidget\Model\ResourceModel\Messenger::class);
    }

    /**
     * @return int
     */
    public function getMessengerId(): int
    {
        return (int)$this->getData(self::MESSENGER_ID);
    }

    /**
     * @param int $messengerId
     */
    public function setMessengerId(int $messengerId): void
    {
        $this->setData(self::MESSENGER_ID, $messengerId);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive): void
    {
        $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return (string)$this->getData(self::CODE);
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->setData(self::CODE, $code);
    }

    /**
     * @return string
     */
    public function getCustomName(): string
    {
        return (string)$this->getData(self::CUSTOM_NAME);
    }

    /**
     * @param string $customName
     */
    public function setCustomName(string $customName): void
    {
        $this->setData(self::CUSTOM_NAME, $customName);
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return (string)$this->getData(self::LINK);
    }

    /**
     * @param string $link
     */
    public function setLink(string $link): void
    {
        $this->setData(self::LINK, $link);
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return (string)$this->getData(self::COMMENT);
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->setData(self::COMMENT, $comment);
    }

    /**
     * @return string
     */
    public function getTooltip(): string
    {
        return (string)$this->getData(self::TOOLTIP);
    }

    /**
     * @param string $tooltip
     */
    public function setTooltip(string $tooltip): void
    {
        $this->setData(self::TOOLTIP, $tooltip);
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return (int)$this->getData(self::SORT_ORDER);
    }

    /**
     * @param int $sortOrder
     */
    public function setSortOrder(int $sortOrder): void
    {
        $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * @return array
     */
    public function getStoreIds(): array
    {
        $storeIds = $this->getData(self::STORE_IDS);

        if ($storeIds) {
            $storeIds = explode(',', $storeIds);
        } else {
            $storeIds = [];
        }

        return $storeIds;
    }

    /**
     * @param array $storeIds
     */
    public function setStoreIds(array $storeIds): void
    {
        $this->setData(self::STORE_IDS, $storeIds);
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return (string)$this->getData(self::ICON);
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon): void
    {
        $this->setData(self::ICON, $icon);
    }
}
