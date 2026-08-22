<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Model;

use Magento\Framework\DataObject;

/**
 * Data Model class for Filter Position wizard
 */
class FilterData extends DataObject
{
    public const ATTRIBUTE_ID = 'attribute_id';

    public const BLOCK_POSITION = 'block_position';

    public const ATTRIBUTE_CODE = 'attribute_code';

    public const POSITION = 'position';

    public const TOP_POSITION = 'top_position';

    public const SIDE_POSITION = 'side_position';

    public const FILTER_CODE = 'filter_code';

    public const LABEL = 'label';

    public const IS_ENABLED = 'enabled';

    public const ALLOWED_KEYS = [
        self::ATTRIBUTE_ID,
        self::BLOCK_POSITION,
        self::ATTRIBUTE_CODE,
        self::POSITION,
        self::TOP_POSITION,
        self::SIDE_POSITION,
        self::FILTER_CODE,
        self::LABEL
    ];

    /**
     * Convert model data types according of their method typification.
     */
    public function typifyData(): void
    {
        foreach ($this->_data as $dataKey => &$dataValue) {
            if ($this->isKeyAllowed($dataKey)) {
                $dataValue = $this->getDataUsingMethod($dataKey);
            }
        }
    }

    /**
     * Is key allowed for hard typization
     * @param string $key
     *
     * @return bool
     */
    public function isKeyAllowed(string $key): bool
    {
        return \in_array($key, self::ALLOWED_KEYS, true);
    }

    /**
     * Override set data for force typization
     *
     * @param string|array $key
     * @param mixed $value
     *
     * @return $this
     */
    public function setData($key, $value = null)
    {
        $isArray = \is_array($key);

        parent::setData($key, $value);

        if ($isArray) {
            $this->typifyData();
        }

        return $this;
    }

    /**
     * @param array $arr
     *
     * @return $this
     */
    public function addData(array $arr)
    {
        parent::addData($arr);

        $this->typifyData();

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAttributeId(): ?int
    {
        $data = $this->getDataByKey(self::ATTRIBUTE_ID);
        if ($data === null) {
            return null;
        }

        return (int) $data;
    }

    /**
     * @param int|null $attributeId
     */
    public function setAttributeId(?int $attributeId): void
    {
        $this->setData(self::ATTRIBUTE_ID, $attributeId);
    }

    /**
     * @return bool|null
     */
    public function getIsEnabled(): ?bool
    {
        $data = $this->getDataByKey(self::IS_ENABLED);
        if ($data === null) {
            return null;
        }

        return (bool) $data;
    }

    /**
     * @param bool|null $enabled
     */
    public function setIsEnabled(?bool $enabled): void
    {
        $this->setData(self::IS_ENABLED, $enabled);
    }

    /**
     * @return int
     */
    public function getBlockPosition(): int
    {
        return (int) $this->getDataByKey(self::BLOCK_POSITION);
    }

    /**
     * @param int|null $blockPosition
     */
    public function setBlockPosition(?int $blockPosition): void
    {
        $this->setData(self::BLOCK_POSITION, $blockPosition);
    }

    /**
     * @return string|null
     */
    public function getAttributeCode(): ?string
    {
        $data = $this->getDataByKey(self::ATTRIBUTE_CODE);
        if ($data === null) {
            return null;
        }

        return (string) $data;
    }

    /**
     * @param string|null $attributeCode
     */
    public function setAttributeCode(?string $attributeCode): void
    {
        $this->setData(self::ATTRIBUTE_CODE, $attributeCode);
    }

    /**
     * @return int|null
     */
    public function getPosition(): int
    {
        return (int) $this->getDataByKey(self::POSITION);
    }

    /**
     * @param int|null $position
     */
    public function setPosition(?int $position): void
    {
        $this->setData(self::POSITION, $position);
    }

    /**
     * @return int
     */
    public function getTopPosition(): int
    {
        return (int) $this->getDataByKey(self::TOP_POSITION);
    }

    /**
     * @param int $topPosition
     */
    public function setTopPosition(int $topPosition): void
    {
        $this->setData(self::TOP_POSITION, $topPosition);
    }

    /**
     * @return int
     */
    public function getSidePosition(): int
    {
        return (int) $this->getDataByKey(self::SIDE_POSITION);
    }

    /**
     * @param int $sidePosition
     */
    public function setSidePosition(int $sidePosition): void
    {
        $this->setData(self::SIDE_POSITION, $sidePosition);
    }

    /**
     * @return string|null
     */
    public function getFilterCode(): ?string
    {
        $data = $this->getDataByKey(self::FILTER_CODE);
        if ($data === null) {
            return null;
        }

        return (string) $data;
    }

    /**
     * @param string|null $filterCode
     */
    public function setFilterCode(?string $filterCode): void
    {
        $this->setData(self::FILTER_CODE, $filterCode);
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return (string) $this->getDataByKey(self::LABEL);
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->setData(self::LABEL, $label);
    }
}
