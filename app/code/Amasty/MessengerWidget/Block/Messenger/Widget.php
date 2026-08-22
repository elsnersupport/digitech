<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Block\Messenger;

use Amasty\MessengerWidget\Model\ConfigProvider;
use Amasty\MessengerWidget\Api\Data\MessengerInterface;
use Amasty\MessengerWidget\Model\Config\Source\MessengerCode;
use Amasty\MessengerWidget\Model\Icon\Uploader;
use Amasty\MessengerWidget\Model\Messenger;
use Amasty\MessengerWidget\Model\Messenger\Form\DefaultIcon\Mapper;
use Amasty\MessengerWidget\Model\ResourceModel\Messenger\CollectionFactory;
use Magento\Framework\View\Element\Template;

class Widget extends Template
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var MessengerCode
     */
    private $messengerCode;

    /**
     * @var Uploader
     */
    private $uploader;

    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @var Template\Context
     */
    private $context;

    public function __construct(
        ConfigProvider $configProvider,
        CollectionFactory $collectionFactory,
        MessengerCode $messengerCode,
        Uploader $uploader,
        Mapper $mapper,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->collectionFactory = $collectionFactory;
        $this->messengerCode = $messengerCode;
        $this->uploader = $uploader;
        $this->mapper = $mapper;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getJsLayout(): string
    {
        $result = &$this->jsLayout['components']['ammessenger-widget'];
        $result['widgetPosition'] = $this->configProvider->getWidgetPosition();
        $messengers = $this->collectionFactory->create()
            ->addFieldToFilter(MessengerInterface::IS_ACTIVE, 1)
            ->addStoresFilter((int)$this->context->getStoreManager()->getStore()->getId())
            ->setOrder(MessengerInterface::SORT_ORDER, 'DESC')
            ->getItems();
        $messengesTitles = $this->messengerCode->toOptionArray();
        $imageMaps = $this->mapper->getArrayDefaultImagesMap();
        /** @var Messenger $messenger */
        foreach ($messengers as $messenger) {
            $result['messengers'][] = [
                'title' => $this->resolveTitleForMessenger($messenger, $messengesTitles),
                'link' => $messenger->getLink(),
                'image_src' => $this->resolveIconsForMessenger($messenger, $imageMaps),
                'tooltip' => $messenger->getTooltip()
            ];
        }
        $result['children']['ammessenger-privacy-policy']['privacyPolicyContent'] =
            $this->_escaper->escapeHtml(
                $this->configProvider->getPrivacyPolicyText(),
                $this->configProvider->getWhiteListTags()
            );

        if ($this->configProvider->isPrivacyPolicyEnabled()) {
            $result['children']['ammessenger-privacy-policy']['isPrivacyPolicyEnabled'] = true;
        }

        return parent::getJsLayout();
    }

    /**
     * @param Messenger $messenger
     * @param array $messengesTitles
     * @return string
     */
    private function resolveTitleForMessenger(Messenger $messenger, array $messengesTitles): string
    {
        $result = '';
        if ($messenger->getCode() === MessengerCode::OTHER) {
            $result = $messenger->getCustomName();
        } else {
            foreach ($messengesTitles as $title) {
                if ($messenger->getCode() === $title['value']) {
                    $result = $title['label']->render();
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @param Messenger $messenger
     * @param array $imageMaps
     * @return array
     */
    private function resolveIconsForMessenger(Messenger $messenger, array $imageMaps): array
    {
        if (($messenger->getCode() === MessengerCode::OTHER) && !empty($messenger->getIcon())) {
            $fileInfo = $this->uploader->getFileInfo($messenger->getIcon());
            $result = [
                $fileInfo['url']
            ];
            if ($fileInfo['extension'] !== 'svg') {
                $result[] = $fileInfo['retinaUrl'];
            }
        } else {
            $result = [$imageMaps[$messenger->getCode()]];
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isShowMessengers(): bool
    {
        return $this->configProvider->isEnabled();
    }
}
