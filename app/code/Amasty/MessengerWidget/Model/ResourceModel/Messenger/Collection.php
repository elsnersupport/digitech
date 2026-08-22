<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\ResourceModel\Messenger;

use Amasty\MessengerWidget\Model\Messenger;
use Amasty\MessengerWidget\Api\Data\MessengerInterface;
use Amasty\MessengerWidget\Model\ResourceModel\Messenger as MessengerResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;

class Collection extends AbstractCollection
{
    /**
     * @var bool
     */
    private $isStoresJoined = false;

    /**
     * @var string
     */
    protected $_idFieldName = MessengerInterface::MESSENGER_ID;

    protected function _construct()
    {
        $this->_init(
            Messenger::class,
            MessengerResource::class
        );
    }

    /**
     * @return $this|Collection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinStores();

        return $this;
    }

    /**
     * @return $this
     */
    public function joinStores(): Collection
    {
        if ($this->isStoresJoined) {
            return $this;
        }
        $this->isStoresJoined = true;
        $this->getSelect()->joinLeft(
            ['cs' => $this->getTable(MessengerResource::STORES_TABLE)],
            'main_table.messenger_id = cs.messenger_id',
            ['store_ids' => new \Zend_Db_Expr('GROUP_CONCAT(cs.store_id)')]
        );
        $this->getSelect()->group('main_table.messenger_id');

        return $this;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function addStoresFilter(int $storeId): Collection
    {
        $this->joinStores();
        $stores = [Store::DEFAULT_STORE_ID];

        if ($storeId) {
            $stores[] = $storeId;
        }

        $this->addFieldToFilter('cs.store_id', $stores);

        return $this;
    }

    /**
     * Add field filter to collection
     *
     * @see self::_getConditionSql for $condition
     *
     * @param string|array $field
     * @param null|string|array $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null): Collection
    {
        if ($field === 'store_id') {
            $field = 'cs.store_id';
        }

        if (strpos($field, '.') === false) {
            $field = 'main_table.' . $field;
        }
        return parent::addFieldToFilter($field, $condition);
    }
}
