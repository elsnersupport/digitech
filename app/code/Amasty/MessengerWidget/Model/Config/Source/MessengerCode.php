<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\Config\Source;

class MessengerCode implements \Magento\Framework\Data\OptionSourceInterface
{
    public const WHATS_APP = 'whats_app';
    public const FACEBOOK_MESSENGER = 'facebook_messenger';
    public const SNAPCHAT = 'snapchat';
    public const SKYPE = 'skype';
    public const DISCORD = 'discord';
    public const VIBER = 'viber';
    public const TELEGRAM = 'Telegram';
    public const KIK = 'kik';
    public const LINE = 'line';
    public const WE_CHAT = 'we_chat';
    public const KAKAO_TALK = 'kakao_talk';
    public const TANGO = 'tango';
    public const IMO = 'imo';
    public const INSTAGRAM = 'instagram';
    public const TWITTER = 'twitter';
    public const QQ = 'qq';
    public const THREEMA = 'threema';
    public const SIGNAL = 'signal';
    public const WIRE = 'wire';
    public const OTHER = 'other';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => ' '],
            ['value' => self::WHATS_APP, 'label' => __('WhatsApp')],
            ['value' => self::FACEBOOK_MESSENGER, 'label' => __('Facebook Messenger')],
            ['value' => self::SNAPCHAT, 'label' => __('Snapchat')],
            ['value' => self::SKYPE, 'label' => __('Skype')],
            ['value' => self::DISCORD, 'label' => __('Discord')],
            ['value' => self::VIBER, 'label' => __('Viber')],
            ['value' => self::TELEGRAM, 'label' => __('Telegram')],
            ['value' => self::KIK, 'label' => __('Kik')],
            ['value' => self::LINE, 'label' => __('Line')],
            ['value' => self::WE_CHAT, 'label' => __('WeChat')],
            ['value' => self::KAKAO_TALK, 'label' => __('KakaoTalk')],
            ['value' => self::TANGO, 'label' => __('Tango')],
            ['value' => self::IMO, 'label' => __('imo')],
            ['value' => self::INSTAGRAM, 'label' => __('Instagram')],
            ['value' => self::TWITTER, 'label' => __('Twitter')],
            ['value' => self::QQ, 'label' => __('QQ')],
            ['value' => self::THREEMA, 'label' => __('Threema')],
            ['value' => self::SIGNAL, 'label' => __('Signal')],
            ['value' => self::WIRE, 'label' => __('Wire')],
            ['value' => self::OTHER, 'label' => __('Other')],
        ];
    }
}
