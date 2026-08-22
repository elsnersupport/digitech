<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Controller\Adminhtml\Filters;

use Amasty\ShopByQuickConfig\Block\MessageProcessor;
use Amasty\ShopByQuickConfig\Model\FilterAggregation\ManageFilterable;
use Amasty\ShopByQuickConfig\Model\FilterCollectionBuilder;
use Amasty\ShopByQuickConfig\Model\ResourceModel\FilterAggregation;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class ChangeFilterable extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_ShopByQuickConfig::navigation_attributes';

    /**
     * @var MessageProcessor
     */
    private $messageProcessor;

    /**
     * @var FilterCollectionBuilder
     */
    private $collectionAdapterBuilder;

    /**
     * @var ManageFilterable
     */
    private $manageFilterable;

    public function __construct(
        Context $context,
        MessageProcessor $messageProcessor,
        FilterCollectionBuilder $collectionAdapterBuilder,
        ManageFilterable $manageFilterable
    ) {
        parent::__construct($context);
        $this->messageProcessor = $messageProcessor;
        $this->collectionAdapterBuilder = $collectionAdapterBuilder;
        $this->manageFilterable = $manageFilterable;
    }

    public function execute()
    {
        $response = [];
        $selected = $this->getRequest()->getParam('selected');

        if (is_array($selected) && !empty($selected)) {
            $this->modifySelectedFilters($selected);
        } else {
            $this->messageManager->addErrorMessage(__('Empty Request'));
        }

        /** @var Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response['messages'] = $this->messageProcessor->getMessagesArray();
        $result->setData($response);

        return $result;
    }

    /**
     * @param $selected
     */
    private function modifySelectedFilters($selected): void
    {
        try {
            $collection = $this->collectionAdapterBuilder->build();
            $collection->addFieldToFilter('id', ['in' => array_keys($selected)]);

            foreach ($collection->getItems() as $filter) {
                $filter->setData(FilterAggregation::IS_FILTERABLE, $selected[$filter->getId()]);
                $this->manageFilterable->execute($filter);
            }
            $this->messageManager->addSuccessMessage(__('Configuration was saved successfully'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Error. Please see the log for more information.')
            );
        }
    }
}
