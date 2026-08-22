<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\ResourceModel\Messenger\Grid\Collection\DataFiller;

use Amasty\MessengerWidget\Api\Data\MessengerInterface;
use Amasty\MessengerWidget\Model\ResourceModel\Collection\DataFillerInterface;
use Amasty\MessengerWidget\Model\ResourceModel\Messenger;
use Amasty\MessengerWidget\Model\ResourceModel\Messenger\Grid\Collection;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Store implements DataFillerInterface
{
    /**
     * @param AbstractCollection|Collection $collection
     * @return void
     */
    public function attachData(AbstractCollection $collection): void
    {
        $stores = $this->getStores($collection);

        if (!empty($stores)) {
            foreach ($collection->getItems() as $item) {
                $messengerId = $item->getMessengerId();
                $messengerStores = $stores[$messengerId] ?? [];
                $item->setData('store_id', $messengerStores);
            }
        }
    }

    /**
     * @param AbstractCollection $collection
     * @return array
     */
    private function getStores(AbstractCollection $collection): array
    {
        $messengerIds = $collection->getColumnValues(MessengerInterface::MESSENGER_ID);
        $stores = [];

        if (!empty($messengerIds)) {
            $select = $collection->getConnection()->select()
                ->from($collection->getTable(Messenger::STORES_TABLE))
                ->where(MessengerInterface::MESSENGER_ID . ' IN(?)', $messengerIds);

            $data = (array)$collection->getConnection()->fetchAll($select);

            foreach ($data as $itemData) {
                $messengerId = $itemData[MessengerInterface::MESSENGER_ID];
                $stores[$messengerId][] = $itemData['store_id'];
            }
        }

        return $stores;
    }
}
