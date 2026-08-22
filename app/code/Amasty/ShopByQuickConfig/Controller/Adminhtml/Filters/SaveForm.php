<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Controller\Adminhtml\Filters;

use Amasty\Shopby\Model\UrlBuilder\Adapter;
use Amasty\ShopByQuickConfig\Block\MessageProcessor;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Config\Controller\Adminhtml\System\Config\Save as SystemSaveController;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\MessageInterface;

class SaveForm extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_ShopByQuickConfig::navigation_attributes';

    /**
     * @var SystemSaveController
     */
    private $configController;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var MessageProcessor
     */
    private $messageProcessor;

    public function __construct(
        Context $context,
        SystemSaveController $configController,
        ProductAttributeRepositoryInterface $attributeRepository,
        MessageProcessor $messageProcessor
    ) {
        parent::__construct($context);
        $this->configController = $configController;
        $this->attributeRepository = $attributeRepository;
        $this->messageProcessor = $messageProcessor;
    }

    public function execute()
    {
        $response = [];
        if ($this->getRequest()->getParam('attribute_code')) {
            $this->saveAttribute();
        } else {
            $this->saveConfig();
        }

        /** @var Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $response['messages'] = $this->messageProcessor->getMessagesArray();

        $result->setData($response);

        return $result;
    }

    /**
     * Emulate save attribute filter.
     * Can't call controller directly because controller set default values.
     */
    private function saveAttribute(): void
    {
        try {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            $attribute = $this->attributeRepository->get($this->getRequest()->getParam('attribute_code'));
            $data = $this->getRequest()->getParams();
            unset($data['attribute_id'], $data['attribute_code'], $data['frontend_input']);
            $attribute->addData($data);
            // Deprecation ignored for emulate Attribute controller behavior
            $attribute->save();

            $this->messageManager->addSuccessMessage(
                __('The changes for "%1" were saved successfully.', $attribute->getDefaultFrontendLabel())
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
        }
    }

    /**
     * Emulate save system configuration controller.
     */
    private function saveConfig(): void
    {
        $request = $this->getRequest();
        $data = $request->getParams();
        $data['section'] = Adapter::SELF_MODULE_NAME;
        $request->setParams($data);

        $this->configController->dispatch($request);

        $this->replaceSuccessMessage();
    }

    private function replaceSuccessMessage(): void
    {
        $label = $this->getLabel();
        $lastMessage = $this->messageManager->getMessages(false)->getLastAddedMessage();
        if ($label && $lastMessage && $lastMessage->getType() === MessageInterface::TYPE_SUCCESS) {
            $lastMessage->setText(__('The changes for "%1" were saved successfully.', $label));
        }
    }

    /**
     * Get label from post data of system configuration filter group
     *
     * @return null|string
     */
    private function getLabel(): ?string
    {
        $data = current($this->getRequest()->getParam('groups', []));

        return $data['fields']['label']['value'] ?? null;
    }
}
