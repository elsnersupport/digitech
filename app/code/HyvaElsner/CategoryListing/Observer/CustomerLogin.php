<?php

namespace HyvaElsner\CategoryListing\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;

class CustomerLogin implements ObserverInterface
{
    public const CUST_EM = "cust_em";

    public const CUST_PH = "cust_ph";
    
    public const DURATION = 8640000;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;
    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepositoryInterface;

    /**
     * plugin constructor
     * 
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SessionManagerInterface $sessionManager
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param AddressRepositoryInterface $addressRepositoryInterface
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        CustomerRepositoryInterface $customerRepositoryInterface,
        AddressRepositoryInterface $addressRepositoryInterface
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->addressRepositoryInterface = $addressRepositoryInterface;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $customerId = $customer->getId();
        $customerData = $this->customerRepositoryInterface->getById($customerId);
        $customerEmail = $customerData->getEmail();
        $telephoneNo = "1234567890";
        $billingAddressId = $customerData->getDefaultBilling();
        if($billingAddressId){
            $billingAddress = $this->addressRepositoryInterface->getById($billingAddressId);
            $telephoneNo = $billingAddress->getTelephone();
        }

        $this->deleteCookieData();

        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(self::DURATION)
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain());
        $this->cookieManager->setPublicCookie(
            self::CUST_EM,
            $customerEmail,
            $metadata
        );
        $this->cookieManager->setPublicCookie(
            self::CUST_PH,
            $telephoneNo,
            $metadata
        );
       
    }

    public function deleteCookieData()
    {
        $this->cookieManager->deleteCookie(
            self::CUST_EM,
            $this->cookieMetadataFactory
                ->createCookieMetadata()
                ->setPath($this->sessionManager->getCookiePath())
                ->setDomain($this->sessionManager->getCookieDomain())
        );

        $this->cookieManager->deleteCookie(
            self::CUST_PH,
            $this->cookieMetadataFactory
                ->createCookieMetadata()
                ->setPath($this->sessionManager->getCookiePath())
                ->setDomain($this->sessionManager->getCookieDomain())
        );
    }
}