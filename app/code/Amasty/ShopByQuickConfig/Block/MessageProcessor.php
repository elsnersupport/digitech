<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Block;

use Magento\Framework\Message\ManagerInterface;

class MessageProcessor
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    public function __construct(ManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    /**
     * Prepare messages for message component.
     *
     * @return array
     */
    public function getMessagesArray(): array
    {
        $result = [];

        foreach ($this->messageManager->getMessages(true)->getItems() as $message) {
            $result[] = [
                'message' => $message->getText(),
                'type' => $message->getType()
            ];
        }

        return $result;
    }
}
