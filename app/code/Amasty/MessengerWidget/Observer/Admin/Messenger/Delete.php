<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Observer\Admin\Messenger;

use Amasty\MessengerWidget\Model\Icon\Uploader;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Delete implements ObserverInterface
{
    /**
     * @var Uploader
     */
    private $uploader;

    public function __construct(Uploader $uploader)
    {
        $this->uploader = $uploader;
    }

    /**
     * Event name 'amasty_messenget_widget_messenger_delete_after'
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $messenger = $observer->getObject();
        if ($messenger->getIcon()) {
            $this->uploader->remove($messenger->getIcon());
        }
    }
}
