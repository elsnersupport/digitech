<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Api;

interface MessengerSaveInterface
{
    /**
     * @param \Amasty\MessengerWidget\Api\Data\MessengerInterface $messenger
     * @return \Amasty\MessengerWidget\Api\Data\MessengerInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(
        \Amasty\MessengerWidget\Api\Data\MessengerInterface $messenger
    ): \Amasty\MessengerWidget\Api\Data\MessengerInterface;
}
