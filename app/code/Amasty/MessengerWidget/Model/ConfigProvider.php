<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    /**
     * xpath prefix of module (section)
     * @var string '{section}/'
     */
    protected $pathPrefix = 'am_messenger_widget/';

    private const IS_ENABLED = 'general/is_enabled';
    private const WIDGET_POSITION = 'general/widget_position';
    private const WHITELIST_TAGS = 'general/whitelist_tags';
    private const IS_PRIVACY_POLICY_ENABLED = 'privacy_policy/is_privacy_policy_enabled';
    private const PRIVACY_POLICY_TEXT = 'privacy_policy/privacy_policy_text';

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isSetFlag(self::IS_ENABLED);
    }

    /**
     * @return string
     */
    public function getWidgetPosition(): string
    {
        return (string)$this->getValue(self::WIDGET_POSITION);
    }

    /**
     * @return bool
     */
    public function isPrivacyPolicyEnabled(): bool
    {
        return $this->isSetFlag(self::IS_PRIVACY_POLICY_ENABLED);
    }

    /**
     * @return string
     */
    public function getPrivacyPolicyText(): string
    {
        return (string)$this->getValue(self::PRIVACY_POLICY_TEXT);
    }

    /**
     * @return array
     */
    public function getWhiteListTags(): array
    {
        return explode(',', $this->getValue(self::WHITELIST_TAGS));
    }
}
