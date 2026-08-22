<?php

namespace Meetanshi\OrderTracking\Controller\OrderTracking;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\UrlInterface;

/**
 * Class View
 * @package Meetanshi\OrderTracking\Controller\OrderTracking
 */
class View extends \Magento\Framework\App\Action\Action
{
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
     * @var OrderFactory
     */
    protected $orderFactory;
    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param LayoutFactory $layoutFactory
     * @param OrderFactory $orderFactory
     * @param UrlInterface $urlInterface
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        LayoutFactory $layoutFactory,
        OrderFactory $orderFactory,
        UrlInterface $urlInterface
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->orderFactory = $orderFactory;
        $this->layoutFactory = $layoutFactory;
        $this->urlInterface = $urlInterface;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $actionUrl = $this->urlInterface->getCurrentUrl();
        $pathinfo = pathinfo($actionUrl);
        $ordertracking = $pathinfo['basename'];

        $orderdata = $this->orderFactory->create()->getCollection()->
        addAttributeToFilter('increment_id', $ordertracking)->getData();

        if (is_array($orderdata) && !empty($orderdata)) {
            $order = $this->orderFactory->create()->load($orderdata[0]['entity_id']);

            if ($order->getId()) {
                $this->registry->register('current_order', $order);
                return $this->resultPageFactory->create();
            } else {
                $this->messageManager->addErrorMessage(__('Order Not Found.Please try again later'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            }
        } else {
            $this->messageManager->addErrorMessage(__('Order Not Found.Please try again later'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
    }
}
