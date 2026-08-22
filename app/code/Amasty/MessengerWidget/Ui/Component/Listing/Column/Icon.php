<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Ui\Component\Listing\Column;

use Amasty\MessengerWidget\Api\Data\MessengerInterface;
use Amasty\MessengerWidget\Model\Config\Source\MessengerCode;
use Amasty\MessengerWidget\Model\Icon\ResizerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Setup\Exception;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Icon extends Column
{
    /**
     * @var Repository
     */
    private $assetRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Repository $assetRepository,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->assetRepository = $assetRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param array $dataSource
     * @return array|void
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = $this->getMessengerIcon($item);
            }
        }

        return $dataSource;
    }

    /**
     * @param array $item
     * @return string
     */
    private function getMessengerIcon(array $item): string
    {
        $messengerCode = $item[MessengerInterface::CODE] ?? '';
        $fileId = 'Amasty_MessengerWidget::images/' . $messengerCode . '.svg';
        $params = [
            'area' => 'frontend'
        ];
        $asset = $this->assetRepository->createAsset($fileId, $params);

        try {
            $img = "<img width='60' height='60' src='" . $asset->getUrl() . "'>";
        } catch (Exception $e) {
            $img = '';
        }

        if ($messengerCode === MessengerCode::OTHER) {
            $baseUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $img = "<img width='60' height='60' src='" .
                $baseUrl . ResizerInterface::UPLOAD_DIR . '/' . $item['icon'] . "'>";
        }

        return (string)$img;
    }
}
