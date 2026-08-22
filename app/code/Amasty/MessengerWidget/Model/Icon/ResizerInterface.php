<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\Icon;

interface ResizerInterface
{
    public const BASE_IMAGE_SIZE = 60;

    public const RETINA_IMAGE_SIZE = 120;
    
    public const MINIMUM_UPLOAD_SIZE = 120;

    public const UPLOAD_DIR = 'ammessengerwidget';

    public const UPLOAD_DIR_RETINA = 'ammessengerwidget/retina';

    /**
     * @param string $file
     * @return void
     */
    public function execute(string $file): void;
}
