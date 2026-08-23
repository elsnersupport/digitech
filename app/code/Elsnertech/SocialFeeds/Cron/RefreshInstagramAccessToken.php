<?php
namespace Elsnertech\SocialFeeds\Cron;

use Elsnertech\SocialFeeds\Helper\Data;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Notification\NotifierInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use RuntimeException;

/**
 * Refreshes the Instagram Basic Display API long-lived access token before it expires.
 *
 * Instagram long-lived tokens are valid for 60 days and Instagram only allows refreshing
 * a token once it is at least 24 hours old. Running this daily keeps the stored token
 * perpetually re-refreshed well inside its validity window, so it never reaches expiry
 * as long as this cron keeps running. An already-expired token cannot be recovered
 * through this endpoint - Instagram requires a full manual re-authorization in that case,
 * which is why a failure here raises an admin notification instead of failing silently.
 */
class RefreshInstagramAccessToken
{
    const REFRESH_URL = 'https://graph.instagram.com/refresh_access_token';

    const MIN_TOKEN_AGE_SECONDS = 86400;

    const EXPIRY_WARNING_WINDOW_SECONDS = 86400;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var NotifierInterface
     */
    protected $notifier;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param Data $helper
     * @param WriterInterface $configWriter
     * @param TypeListInterface $cacheTypeList
     * @param Curl $curl
     * @param NotifierInterface $notifier
     * @param LoggerInterface $logger
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $helper,
        WriterInterface $configWriter,
        TypeListInterface $cacheTypeList,
        Curl $curl,
        NotifierInterface $notifier,
        LoggerInterface $logger,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
        $this->curl = $curl;
        $this->notifier = $notifier;
        $this->logger = $logger;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * Cron entry point
     *
     * @return void
     */
    public function execute()
    {
        $token = $this->helper->getInstagramAccessToken();
        if (!$token) {
            return;
        }

        $lastRefreshed = $this->helper->getTokenLastRefreshedAt();
        if ($lastRefreshed && (time() - strtotime($lastRefreshed)) < self::MIN_TOKEN_AGE_SECONDS) {
            return;
        }

        try {
            $data = $this->requestRefresh($token);

            $newToken = isset($data['access_token']) ? $data['access_token'] : null;
            $expiresIn = isset($data['expires_in']) ? $data['expires_in'] : null;

            if (!$newToken) {
                throw new RuntimeException('No access_token in Instagram response: ' . json_encode($data));
            }

            $now = time();
            $this->configWriter->save(Data::CONFIG_PATH_INSTAGRAM_TOKEN, $newToken);
            $this->configWriter->save(Data::CONFIG_PATH_TOKEN_LAST_REFRESHED, date('Y-m-d H:i:s', $now));

            if ($expiresIn) {
                $this->configWriter->save(
                    Data::CONFIG_PATH_TOKEN_EXPIRES_AT,
                    date('Y-m-d H:i:s', $now + (int) $expiresIn)
                );
            }

            $this->cacheTypeList->cleanType('config');

            $this->logger->info('[Elsnertech_SocialFeeds] Instagram access token refreshed successfully.');
        } catch (Throwable $e) {
            $this->logger->error('[Elsnertech_SocialFeeds] Instagram access token refresh failed: ' . $e->getMessage());
            $this->notifier->addMajor(
                'Instagram access token refresh failed',
                'The Instagram Feed module could not automatically refresh its access token. '
                . 'Generate a new token manually in Stores > Configuration > Instagram Feeds before it expires. '
                . 'Error: ' . $e->getMessage()
            );
        }

        $this->checkExpiryWarning();
    }

    /**
     * Send a warning email if the token is within the warning window of expiring
     * and a warning hasn't already been sent for this specific expiry timestamp.
     *
     * @return void
     */
    protected function checkExpiryWarning()
    {
        $expiresAt = $this->helper->getTokenExpiresAt();
        if (!$expiresAt) {
            return;
        }

        $secondsUntilExpiry = strtotime($expiresAt) - time();
        if ($secondsUntilExpiry > self::EXPIRY_WARNING_WINDOW_SECONDS) {
            return;
        }

        if ($this->helper->getExpiryNotifiedFor() === $expiresAt) {
            return;
        }

        try {
            $this->sendExpiryWarningEmail($expiresAt);
            $this->configWriter->save(Data::CONFIG_PATH_EXPIRY_NOTIFIED_FOR, $expiresAt);
        } catch (Throwable $e) {
            $this->logger->error('[Elsnertech_SocialFeeds] Failed to send token expiry warning email: ' . $e->getMessage());
        }
    }

    /**
     * Send the expiry warning email
     *
     * @param string $expiresAt
     * @return void
     */
    protected function sendExpiryWarningEmail($expiresAt)
    {
        $to = $this->helper->getNotifyEmail();
        if (!$to) {
            return;
        }

        $store = $this->storeManager->getStore();

        $transport = $this->transportBuilder
            ->setTemplateIdentifier('socialfeeds_instagram_token_expiring')
            ->setTemplateOptions([
                'area' => Area::AREA_ADMINHTML,
                'store' => $store->getId(),
            ])
            ->setTemplateVars([
                'store_name' => $store->getName(),
                'expires_at' => $expiresAt,
            ])
            ->setFromByScope('general')
            ->addTo($to)
            ->getTransport();

        $transport->sendMessage();

        $this->logger->info('[Elsnertech_SocialFeeds] Sent Instagram token expiry warning email to ' . $to);
    }

    /**
     * Call Instagram's refresh endpoint for the given token
     *
     * @param string $token
     * @return array
     */
    protected function requestRefresh($token)
    {
        $this->curl->get(self::REFRESH_URL . '?' . http_build_query([
            'grant_type' => 'ig_refresh_token',
            'access_token' => $token,
        ]));

        $status = $this->curl->getStatus();
        $body = $this->curl->getBody();
        $data = json_decode((string) $body, true);
        $data = is_array($data) ? $data : [];

        if ($status !== 200) {
            throw new RuntimeException('HTTP ' . $status . ': ' . $body);
        }

        return $data;
    }
}
