<?php

namespace Meetanshi\OrderTracking\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class TrackingDetails
 * @package Meetanshi\OrderTracking\Block
 */
class TrackingDetails extends Template
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * TrackingDetails constructor.
     * @param Context $context
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
        $this->setTemplate('Meetanshi_OrderTracking::trackingdetails.phtml');
    }

    /**
     * @return mixed
     */
    public function getTrackOrder()
    {
        return $this->registry->registry('current_order');
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function returnBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }
}
