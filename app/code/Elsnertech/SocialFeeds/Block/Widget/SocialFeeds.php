<?php
namespace Elsnertech\SocialFeeds\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\CacheInterface;
use Hyva\Theme\Model\ViewModelRegistry;

class SocialFeeds extends Template implements BlockInterface
{
    protected $_template = "widget/social_feeds.phtml";

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var ViewModelRegistry
     */
    protected $viewModelRegistry;

    const INSTAGRAM_API_URL = 'https://graph.instagram.com/me/media';

    public function __construct(
        Template\Context $context,
        ScopeConfigInterface $scopeConfig,
        Curl $curl,
        Json $json,
        CacheInterface $cache,
        ViewModelRegistry $viewModelRegistry,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->curl = $curl;
        $this->json = $json;
        $this->cache = $cache;
        $this->viewModelRegistry = $viewModelRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Get Instagram Posts
     */
    public function getInstagramPosts()
    {
        $accessToken = $this->getInstagramAccessToken();
        $postsCount = $this->getPostsCount();

        if (!$accessToken) {
            return [];
        }

        $cacheKey = 'social_feeds_instagram_' . md5($accessToken . $postsCount);
        $cachedData = $this->cache->load($cacheKey);
        
        if ($cachedData) {
            return $this->json->unserialize($cachedData);
        }

        try {
            $url = self::INSTAGRAM_API_URL . '?fields=id,media_type,media_url,thumbnail_url,like_count,comments_count,permalink,caption,timestamp&limit=' . $postsCount . '&access_token=' . $accessToken;
            
            $this->curl->get($url);
            $response = $this->curl->getBody();
            $data = $this->json->unserialize($response);
            if (isset($data['data'])) {
                $posts = [];
                foreach ($data['data'] as $post) {
                    if ($post['media_type'] === 'VIDEO') {
                        $mediaUrl = isset($post['thumbnail_url']) ? $post['thumbnail_url'] : $post['media_url'];
                    } else {
                        $mediaUrl = $post['media_url'];
                    }

                    $posts[] = [
                        'id' => $post['id'],
                        'type' => strtolower($post['media_type']),
                        'media_url' => $mediaUrl,
                        'permalink' => $post['permalink'],
                        'caption' => isset($post['caption']) ? $post['caption'] : '',
                        'timestamp' => $post['timestamp'],
                        'comments_count' => isset($post['comments_count']) ? $post['comments_count'] : 0,
                        'like_count' => isset($post['like_count']) ? $post['like_count'] : 0,
                        'platform' => 'instagram'
                    ];
                }

                $cacheTime = $this->getCacheTime() * 60;
                $this->cache->save($this->json->serialize($posts), $cacheKey, [], $cacheTime);
                return $posts;
            }
        } catch (\Exception $e) {
            $this->_logger->error('Instagram API Error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get Instagram feeds as JSON for Alpine.js
     */
    public function getInstagramFeedsJson()
    {
        return $this->json->serialize($this->getInstagramPosts());
    }

    /**
     * Get Instagram Access Token
     */
    protected function getInstagramAccessToken()
    {
        return $this->getData('instagram_access_token') ?: 
               $this->scopeConfig->getValue('social_feeds/instagram/access_token', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Posts Count
     */
    protected function getPostsCount()
    {
        return (int)($this->getData('posts_count') ?: 
               $this->scopeConfig->getValue('social_feeds/general/posts_count', ScopeInterface::SCOPE_STORE) ?: 4);
    }

    /**
     * Get Cache Time
     */
    protected function getCacheTime()
    {
        return (int)($this->scopeConfig->getValue('social_feeds/general/cache_time', ScopeInterface::SCOPE_STORE) ?: 30);
    }

    /**
     * Check if animations are enabled
     */
    public function isAnimationsEnabled()
    {
        return $this->getData('enable_animations') !== null ? 
               (bool)$this->getData('enable_animations') :
               $this->scopeConfig->isSetFlag('social_feeds/general/enable_animations', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if Instagram is enabled
     */
    protected function isInstagramEnabled()
    {
        return $this->scopeConfig->isSetFlag('social_feeds/instagram/enabled', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get relative time string
     */
    public function getRelativeTime($timestamp)
    {
        $time = strtotime($timestamp);
        $diff = time() - $time;
        
        if ($diff < 60) {
            return $diff . ' sec. ago';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' min. ago';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' hr. ago';
        } else {
            return floor($diff / 86400) . ' days ago';
        }
    }

    /**
     * Get HYVA HeroIcons view model
     */
        public function isHyvaTheme()
        {
            return $this->viewModelRegistry !== null;
        }

        public function getHeroicons()
        {
            if ($this->viewModelRegistry && class_exists('\Hyva\Theme\ViewModel\HeroIcons')) {
                try {
                    return $this->viewModelRegistry->require(\Hyva\Theme\ViewModel\HeroIcons::class);
                } catch (\Exception $e) {
                    return null;
                }
            }
            return null;
        }

    /**
     * Get HYVA SvgIcons view model
     */
    public function getSvgIcons()
    {
        return $this->viewModelRegistry->require(\Hyva\Theme\ViewModel\SvgIcons::class);
    }
}