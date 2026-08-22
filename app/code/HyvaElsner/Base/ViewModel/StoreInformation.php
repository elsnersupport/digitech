<?php
namespace HyvaElsner\Base\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Store\Model\StoreManagerInterface;

class StoreInformation implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    public const XML_PATH_ADMIN_EMAIL = 'trans_email/ident_general/email';
    public const XML_PATH_STORE_PHONE = 'general/store_information/phone';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /** @var CountryFactory */
    protected $countryFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param CountryFactory $countryFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CountryFactory $countryFactory,
        StoreManagerInterface $storeManager
        )
    {
        $this->scopeConfig = $scopeConfig;
        $this->countryFactory = $countryFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Get admin email address
     *
     * @return string
     */
    public function getStoreMail()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ADMIN_EMAIL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get store phone number
     *
     * @return string
     */
    public function getStorePhoneNumber()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_STORE_PHONE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get country name
     *
     * @param string $countryCode
     * @return string
     */
    public function getCountryName($countryCode)
    {
        $countryName = '';
        if ($countryCode != "OTHER") {
            $country = $this->countryFactory->create()->loadByCode($countryCode);
            $countryName = $country->getName();
        }
        return $countryName;
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
}
