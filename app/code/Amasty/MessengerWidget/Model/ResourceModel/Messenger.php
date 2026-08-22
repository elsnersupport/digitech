<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\ResourceModel;

use Amasty\MessengerWidget\Api\Data\MessengerInterface;
use Amasty\MessengerWidget\Model\ResourceModel\Messenger as MessengerResource;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Messenger extends AbstractDb
{
    public const TABLE = 'amasty_messenger_widget_messenger';
    public const STORES_TABLE = 'amasty_messenger_widget_messenger_store';
    
    public function _construct()
    {
        $this->_init(self::TABLE, MessengerInterface::MESSENGER_ID);
    }

    /**
     * Add stores to load select
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\DB\Select|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        $mainTable = $this->getMainTable();

        $select->joinLeft(
            ['cs' => $this->getTable(MessengerResource::STORES_TABLE)],
            $mainTable.'.messenger_id = cs.messenger_id',
            ['store_ids' => new \Zend_Db_Expr('GROUP_CONCAT(cs.store_id)')]
        );
        $select->group($mainTable . '.messenger_id');

        return $select;
    }

    /**
     * Save stores after saving main model
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return Messenger
     */
    public function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $storeIds = $object->getData('store_ids');

        if (is_array($storeIds)) {
            $this->saveStores((int)$object->getId(), $storeIds);
        }

        return parent::_afterSave($object);
    }

    /**
     * Update stores table according to new store ids list
     *
     * @param int $messengerId
     * @param int[] $storeIds
     */
    public function saveStores(int $messengerId, array $storeIds): void
    {
        $connection = $this->getConnection();
        $storesTable = $this->getTable(self::STORES_TABLE);
        $connection->delete($storesTable, ['messenger_id = ?' => $messengerId]);

        $insertArray = [];

        foreach ($storeIds as $storeId) {
            $insertArray[] = [
                'messenger_id' => $messengerId,
                'store_id' => $storeId
            ];
        }

        if ($insertArray) {
            $connection->insertMultiple($storesTable, $insertArray);
        }
    }
}
