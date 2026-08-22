<?php
namespace Elsnertech\SocialFeeds\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const CONFIG_PATH_INSTAGRAM_ENABLED = 'social_feeds/instagram/enabled';
    const CONFIG_PATH_INSTAGRAM_TOKEN = 'social_feeds/instagram/access_token';
    const CONFIG_PATH_POSTS_COUNT = 'social_feeds/general/posts_count';
    const CONFIG_PATH_CACHE_TIME = 'social_feeds/general/cache_time';
    const CONFIG_PATH_ENABLE_ANIMATIONS = 'social_feeds/general/enable_animations';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if Instagram is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isInstagramEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_INSTAGRAM_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Instagram Access Token
     *
     * @param int|null $storeId
     * @return string|null
     */
    public function getInstagramAccessToken($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_INSTAGRAM_TOKEN,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get posts count
     *
     * @param int|null $storeId
     * @return int
     */
    public function getPostsCount($storeId = null)
    {
        return (int) $this->scopeConfig->getValue(
            self::CONFIG_PATH_POSTS_COUNT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get cache time in minutes
     *
     * @param int|null $storeId
     * @return int
     */
    public function getCacheTime($storeId = null)
    {
        return (int) $this->scopeConfig->getValue(
            self::CONFIG_PATH_CACHE_TIME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if animations are enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isAnimationsEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_ENABLE_ANIMATIONS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get module version
     *
     * @return string
     */
    public function getModuleVersion()
    {
        return '1.0.0';
    }

    /**
     * Check if HYVA theme is active
     *
     * @return bool
     */
    public function isHyvaTheme()
    {
        return class_exists('\Hyva\Theme\Model\ViewModelRegistry');
    }
}