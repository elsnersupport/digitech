<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Controller\Adminhtml\Filters;

use Amasty\Shopby\Model\Source\FilterPlacedBlock;
use Amasty\ShopByQuickConfig\Block\MessageProcessor;
use Amasty\ShopByQuickConfig\Model\FilterData;
use Amasty\ShopByQuickConfig\Model\FilterSave;
use Amasty\ShopByQuickConfig\Model\FiltersProvider;
use Amasty\ShopByQuickConfig\Ui\DataProvider\QuickForm;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Save Filters position. Return actual data.
 * JSON Response.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_ShopByQuickConfig::navigation_attributes';

    public const SHOPBY_FILTERS_HANDLER = 'amasty_shopby_filters';

    /**
     * @var FiltersProvider
     */
    private $filtersProvider;

    /**
     * @var FilterSave
     */
    private $filterSave;

    /**
     * @var UiComponentFactory
     */
    private $factory;

    /**
     * @var MessageProcessor
     */
    private $messageProcessor;

    public function __construct(
        Context $context,
        FiltersProvider $filtersProvider,
        FilterSave $filterSave,
        UiComponentFactory $factory,
        MessageProcessor $messageProcessor
    ) {
        $this->filtersProvider = $filtersProvider;
        $this->filterSave = $filterSave;
        $this->factory = $factory;
        $this->messageProcessor = $messageProcessor;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $responseContent = [];
        try {
            $this->saveFilters();
            $this->messageManager->addSuccessMessage(__('The item was moved successfully.'));

            $component = $this->factory->create(self::SHOPBY_FILTERS_HANDLER);
            $this->prepareComponent($component);
            $responseContent['data'] = (string)$component->render(); // JSON string
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
        }

        $responseContent['messages'] = $this->messageProcessor->getMessagesArray();

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);

        return $resultJson;
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return void
     */
    private function prepareComponent(UiComponentInterface $component): void
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }

        $component->prepare();
    }

    /**
     * @throws LocalizedException
     */
    private function saveFilters(): void
    {
        $bothPositionItems = [];
        foreach ([QuickForm::KEY_SIDE_ITEMS, QuickForm::KEY_TOP_ITEMS] as $blockKey) {
            foreach ($this->getRequest()->getParam($blockKey, []) as $itemData) {
                if (!isset($itemData[FilterData::FILTER_CODE], $itemData[FilterData::POSITION])) {
                    throw new LocalizedException(__('Invalid Data'));
                }

                $item = $this->filtersProvider->getItemByCode($itemData[FilterData::FILTER_CODE]);
                $position = (int) $itemData[FilterData::POSITION];
                if ($item->getBlockPosition() === FilterPlacedBlock::POSITION_BOTH) {
                    switch ($blockKey) {
                        case QuickForm::KEY_SIDE_ITEMS:
                            $item->setSidePosition($position);
                            break;
                        case QuickForm::KEY_TOP_ITEMS:
                            $item->setTopPosition($position);
                            break;
                    }
                    $bothPositionItems[$item->getFilterCode()] = $item;
                    continue; // filter which displayed in both positions, saves separately.
                }

                if ($item->getPosition() !== $position) {
                    $item->setPosition($position);
                    $this->filterSave->save($item);
                }
            }
        }

        foreach ($bothPositionItems as $filter) {
            $this->filterSave->saveAdditionalPositions($filter);
        }
    }
}
