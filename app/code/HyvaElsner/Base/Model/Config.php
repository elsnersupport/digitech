<?php

declare(strict_types=1);

namespace HyvaElsner\Base\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Config
{
    /** @var StoreManagerInterface */
    public $storeManager;

    /** @var ScopeConfigInterface */
    public $scopeConfig;

    /** @var Json */
    public $serialize;

    /**
     * Constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $serialize
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Json $serialize
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->serialize = $serialize;
    }

    /**
     * Get media url
     *
     * @return string
     */
    public function getMediaUrl(): string
    {
        try {
            return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * Get base url
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        try {
            return $this->storeManager->getStore()->getBaseUrl();
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * Get store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get config value
     *
     * @param string $path
     * @param string $scope
     * @return mixed
     */
    public function getConfigValue($path, $scope)
    {
        return $this->scopeConfig->getValue(
            $path,
            $scope,
            $this->getStoreId()
        );
    }

    public function getValue($path, $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(
            $path,
            $scope
        );
    }
}
