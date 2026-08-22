<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\Messenger;

use Amasty\MessengerWidget\Api\Data\MessengerInterface;
use Amasty\MessengerWidget\Api\Data\MessengerInterfaceFactory;
use Amasty\MessengerWidget\Api\MessengerGetInterface;
use Amasty\MessengerWidget\Model\ResourceModel\Messenger;
use Magento\Framework\Exception\NoSuchEntityException;

class MessengerGet implements MessengerGetInterface
{
    /**
     * @var MessengerInterfaceFactory
     */
    private $messengerFactory;

    /**
     * @var Messenger
     */
    private $messengerResource;

    public function __construct(MessengerInterfaceFactory $messengerFactory, Messenger $messengerResource)
    {
        $this->messengerFactory = $messengerFactory;
        $this->messengerResource = $messengerResource;
    }

    /**
     * @param int $messengerId
     * @return MessengerInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $messengerId): MessengerInterface
    {
        $object = $this->messengerFactory->create();
        $this->messengerResource->load($object, $messengerId);

        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Messenger with id %1 does not exists', $messengerId));
        }

        return $object;
    }
}
