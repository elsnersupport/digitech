<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\Messenger\Form\DefaultIcon;

use Amasty\MessengerWidget\Model\Config\Source\MessengerCode;
use Amasty\MessengerWidget\Model\Icon\Uploader;

class Mapper
{
    /**
     * @var Uploader
     */
    private $uploader;

    public function __construct(Uploader $uploader)
    {
        $this->uploader = $uploader;
    }

    /**
     * @return string[]
     */
    public function getArrayDefaultImagesMap(): array
    {
        $absolutePath = $this->uploader->getAbsolutePathToDefaultImages() . '/';
        $defaultFileFormat = '.svg';

        return [
            MessengerCode::DISCORD => $absolutePath . MessengerCode::DISCORD . $defaultFileFormat,
            MessengerCode::FACEBOOK_MESSENGER => $absolutePath . MessengerCode::FACEBOOK_MESSENGER . $defaultFileFormat,
            MessengerCode::IMO => $absolutePath . MessengerCode::IMO . $defaultFileFormat,
            MessengerCode::INSTAGRAM => $absolutePath . MessengerCode::INSTAGRAM . $defaultFileFormat,
            MessengerCode::KAKAO_TALK => $absolutePath . MessengerCode::KAKAO_TALK . $defaultFileFormat,
            MessengerCode::KIK => $absolutePath . MessengerCode::KIK . $defaultFileFormat,
            MessengerCode::LINE => $absolutePath . MessengerCode::LINE . $defaultFileFormat,
            MessengerCode::QQ => $absolutePath . MessengerCode::QQ . $defaultFileFormat,
            MessengerCode::SIGNAL => $absolutePath . MessengerCode::SIGNAL . $defaultFileFormat,
            MessengerCode::SKYPE => $absolutePath . MessengerCode::SKYPE . $defaultFileFormat,
            MessengerCode::SNAPCHAT => $absolutePath . MessengerCode::SNAPCHAT . $defaultFileFormat,
            MessengerCode::TANGO => $absolutePath . MessengerCode::TANGO . $defaultFileFormat,
            MessengerCode::TELEGRAM => $absolutePath . MessengerCode::TELEGRAM . $defaultFileFormat,
            MessengerCode::THREEMA => $absolutePath . MessengerCode::THREEMA . $defaultFileFormat,
            MessengerCode::TWITTER => $absolutePath . MessengerCode::TWITTER . $defaultFileFormat,
            MessengerCode::VIBER => $absolutePath . MessengerCode::VIBER . $defaultFileFormat,
            MessengerCode::WE_CHAT => $absolutePath . MessengerCode::WE_CHAT . $defaultFileFormat,
            MessengerCode::WHATS_APP => $absolutePath . MessengerCode::WHATS_APP . $defaultFileFormat,
            MessengerCode::WIRE => $absolutePath . MessengerCode::WIRE . $defaultFileFormat
        ];
    }
}
