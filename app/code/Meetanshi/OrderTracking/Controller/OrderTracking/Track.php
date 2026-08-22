<?php

namespace Meetanshi\OrderTracking\Controller\OrderTracking;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\App\Action\Action;
use Magento\Sales\Model\OrderFactory;
use Meetanshi\OrderTracking\Helper\Data as HelperData;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Track
 * @package Meetanshi\OrderTracking\Controller\OrderTracking
 */
class Track extends Action
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var
     */
    protected $resources;
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;
    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * Track constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param OrderFactory $orderFactory
     * @param LayoutFactory $layoutFactory
     * @param HelperData $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        OrderFactory $orderFactory,
        LayoutFactory $layoutFactory,
        HelperData $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->layoutFactory = $layoutFactory;
        $this->orderFactory = $orderFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $layout = $this->layoutFactory->create();
        $post = $this->getRequest()->getParams();
        if ($post) {
            try {
                $error = false;
                if ($post['order_id'] == '') {
                    $error = true;
                }
                if ($post['email'] == '') {
                    $error = true;
                }
                if ($error) {
                    throw new LocalizedException(__('Invalid Data.'));
                }
                $order = $this->orderFactory->create()->load($post['order_id'], 'increment_id');
                $orderEmail = $order->getCustomerEmail();

                if ($orderEmail == trim((string)$post['email'])) {
                    $this->registry->register('current_order', $order);
                } else {
                    $this->registry->register('current_order', $this->orderFactory->create());
                }

                $order = $this->registry->registry('current_order');
                if ($order->getId()) {
                    $output = $layout->createBlock(\Meetanshi\OrderTracking\Block\TrackingDetails::class)->toHtml();
                    $this->getResponse()->appendBody($output);
                    return;
                } else {
                    $this->messageManager->addErrorMessage($this->helper->customMessage());
                    $layout->initMessages();
                    $this->getResponse()->appendBody($layout->getMessagesBlock()->getGroupedHtml());
                    return;
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $layout->initMessages();
                $this->getResponse()->appendBody($layout->getMessagesBlock()->getGroupedHtml());
                return;
            }
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
    }
}
