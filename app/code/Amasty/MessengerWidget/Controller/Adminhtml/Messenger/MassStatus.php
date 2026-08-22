<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Controller\Adminhtml\Messenger;

use Amasty\MessengerWidget\Model\ResourceModel\Messenger\CollectionFactory;
use Amasty\MessengerWidget\Model\Messenger;
use Amasty\MessengerWidget\Model\Messenger\MessengerSave;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassStatus extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_MessengerWidget::messenger_manage';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var MessengerSave
     */
    private $messengerSave;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        MessengerSave $messengerSave,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->messengerSave = $messengerSave;
        $this->logger = $logger;
    }

    /**
     * Mass update status action
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $status = $this->getRequest()->getParam('status');
        $updatedCount = 0;

        if ($status !== null) {
            /** @var Messenger $messenger */
            foreach ($collection->getItems() as $messenger) {
                try {
                    $messenger->setIsActive((bool)$status);
                    $this->messengerSave->execute($messenger);
                    $updatedCount++;
                } catch (LocalizedException $exception) {
                    $this->logger->error($exception->getLogMessage());
                }
            }
        }

        if ($updatedCount) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been updated.', $updatedCount)
            );
        } else {
            $this->messageManager->addErrorMessage(
                __('No record(s) have been updated.', $updatedCount)
            );
        }

        return $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT)
            ->setPath('*/*/index');
    }
}
