<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Api;

interface MessengerGetInterface
{
    /**
     * @param int $messengerId
     * @return \Amasty\MessengerWidget\Api\Data\MessengerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(int $messengerId): \Amasty\MessengerWidget\Api\Data\MessengerInterface;
}
