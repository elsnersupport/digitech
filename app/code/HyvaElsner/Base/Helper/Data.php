<?php


namespace HyvaElsner\Base\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param Http $request
     * @param StoreManagerInterface $storeManager
     * @param ReviewFactory $reviewFactory
     */
    public function __construct(
        Context $context,
        Http $request,
        StoreManagerInterface $storeManager,
        ReviewFactory $reviewFactory
    ) {
        $this->request          = $request;
        $this->storeManager     = $storeManager;
        $this->reviewFactory    = $reviewFactory;
        parent::__construct($context);
    }

    /**
     * Your comment getCurrentStoreId function
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentStoreId()
    {
        $storeId = $this->request->getParam('store_id');
        if ($storeId) {
            $result = $storeId;
        } else {
            $result = $this->storeManager->getStore()->getId();
        }
        return $result;
    }

    /**
     * Your comment getStoreBaseUrl function
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get Rating Summary
     *
     * @param mixed $product
     */
    public function getRatingSummary($product)
    {
        $this->reviewFactory->create()->getEntitySummary($product, $this->getCurrentStoreId());
        $ratingSummary = $product->getRatingSummary()->getRatingSummary();
        return $ratingSummary;
    }

    /**
     * Store ID
     *
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Store Code
     *
     * @return mixed
     */
    public function getStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }

    /**
     * Get Store Phone Number
     *
     * @return mixed
     */
    public function getStorePhone()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/phone',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }
}
