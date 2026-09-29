<?php
/**
 * Elsnertech_DealsCategory
 *
 * @category    Elsnertech
 * @package     Elsnertech_DealsCategory
 * @author      Elsnertech
 * @copyright   Copyright (c) 2026 Elsnertech
 */
declare(strict_types=1);

namespace Elsnertech\DealsCategory\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Reads the module's admin configuration (default scope only: category assignments are global).
 */
class Config
{
    public const XML_PATH_ENABLED = 'elsnertech_deals_category/general/enabled';
    public const XML_PATH_CATEGORY_ID = 'elsnertech_deals_category/general/category_id';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Whether the scheduled (cron) sync is switched on.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED);
    }

    /**
     * The category that holds the deals, or null when it has not been configured.
     *
     * @return int|null
     */
    public function getCategoryId(): ?int
    {
        $categoryId = (int)$this->scopeConfig->getValue(self::XML_PATH_CATEGORY_ID);

        return $categoryId > 0 ? $categoryId : null;
    }
}
