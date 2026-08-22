<?php

namespace HyvaElsner\CategoryListing\ViewModel;

use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Stdlib\CookieManagerInterface;

class CategoryData implements ArgumentInterface
{
    public const CUST_EM = "cust_em";

    public const CUST_PH = "cust_ph";

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepositoryInterface;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * Constructor
     *
     * @param CategoryRepository $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param CheckoutSession $checkoutSession
     * @param ProductRepositoryInterface $productRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        StoreManagerInterface $storeManager,
        CheckoutSession $checkoutSession,
        ProductRepositoryInterface $productRepository,
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepositoryInterface,
        AddressRepositoryInterface $addressRepositoryInterface,
        CookieManagerInterface $cookieManager,
        HttpContext $httpContext
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->addressRepositoryInterface = $addressRepositoryInterface;
        $this->cookieManager = $cookieManager;
        $this->httpContext = $httpContext;
    }

    /**
     * Get category name by ID
     *
     * @param int $categoryId
     * @return string|null
     */
    public function getCategoryNameById($categoryId)
    {
        try {
            $category = $this->categoryRepository->get($categoryId);
            return $category->getName();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null; // Category not found
        }
    }

    public function getCurrencyCode()
    {
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        return $currencyCode;
    }

    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    public function getProductById($productId)
    {
        return $this->productRepository->getById($productId);
    }

    public function getOrder($orderId)
    {
        return $this->orderRepository->get($orderId);
    }

    public function getCustomerPhoneNo()
    {
        $telephoneNo = "1234567890";
        try {
            $customerId = $this->getCurrentCustomerId();
            if ($customerId) {
                $customer = $this->customerRepositoryInterface->getById($customerId);
                $billingAddressId = $customer->getDefaultBilling();
                $shippingAddressId = $customer->getDefaultShipping();
                $billingAddress = $this->addressRepositoryInterface->getById($billingAddressId);
                $telephoneNo = $billingAddress->getTelephone();
            }
            return $telephoneNo;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $telephoneNo;
        }
    }

    /**
     * Get data from cookie set in remote address
     *
     * @return value
     */
    public function getTeleCookieData()
    {
        return $this->cookieManager->getCookie(self::CUST_PH);
    }

    /**
     * Get data from cookie set in remote address
     *
     * @return value
     */
    public function getEmailCookieData()
    {
        return $this->cookieManager->getCookie(self::CUST_EM);
    }

    public function getCustomerEmail()
    {
        $customerEmail = "test@gmail.com";
        try {
            $customerId = $this->getCurrentCustomerId();
            if ($customerId) {
                $customer = $this->customerRepositoryInterface->getById($customerId);
                $customerEmail = $customer->getEmail();
            }
            return $customerEmail;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $customerEmail;
        }
    }
    public function getCurrentCustomerId()
    {
        $customerId = "0";
        try {
            if ($this->isCustomerLoggedId()) {
                $customerId = $this->httpContext->getValue('customer_id');
            }
            return $customerId;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $customerId;
        }
    }
    public function isCustomerLoggedId()
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }
}
