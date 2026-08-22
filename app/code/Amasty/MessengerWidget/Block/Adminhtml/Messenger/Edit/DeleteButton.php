<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Block\Adminhtml\Messenger\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getButtonData(): array
    {
        $data = [];
        if ($this->getMessengerId()) {
            $data = [
                'label' => __('Delete Messenger'),
                'class' => 'delete',
                'data_attribute' => [
                    'url' => $this->getDeleteUrl()
                ],
                'on_click' => 'deleteConfirm(\'' . __('Are you sure you want to do this?')
                    . '\', \'' . $this->getDeleteUrl() . '\', {"data": {}})',
                'sort_order' => 20,
                'aclResource' => 'Amasty_MessengerWidget::messenger_manage',
            ];
        }

        return $data;
    }

    /**
     * URL to send delete requests to.
     *
     * @return string
     */
    public function getDeleteUrl(): string
    {
        return $this->context->getUrlBuilder()->getUrl('*/*/delete', ['messenger_id' => $this->getMessengerId()]);
    }

    /**
     * @return int
     */
    public function getMessengerId(): int
    {
        $id = $this->context->getRequest()->getParam('messenger_id');

        return (int)$id;
    }
}
