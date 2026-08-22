<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\Messenger\Form;

use Amasty\MessengerWidget\Api\Data\MessengerInterface;
use Amasty\MessengerWidget\Model\ResourceModel\Messenger\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider
{
    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var PoolInterface|null
     */
    private $pool;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        PoolInterface $pool = null,
        DataPersistorInterface $dataPersistor,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->pool = $pool;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $this->loadedData = [];
        $items = $this->collection->getItems();
        /** @var MessengerInterface $messenger */
        foreach ($items as $messenger) {
            $messengerId = $messenger->getMessengerId();
            $data = $messenger->getData();
            $this->loadedData[$messengerId] = $data;
        }

        $data = $this->dataPersistor->get('am_messenger_widget_messenger');
        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $messengerId = $model->getMessengerId() ?: null;
            $this->loadedData[$messengerId] = $model->getData();
            $this->dataPersistor->clear('am_messenger_widget_messenger');
        }

        if ($this->pool) {
            foreach ($this->pool->getModifiersInstances() as $modifier) {
                $this->loadedData = $modifier->modifyData($this->loadedData);
            }
        }

        return $this->loadedData;
    }
}
