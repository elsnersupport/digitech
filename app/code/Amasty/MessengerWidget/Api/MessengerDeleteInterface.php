<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Api;

interface MessengerDeleteInterface
{
    /**
     * @param \Amasty\MessengerWidget\Api\Data\MessengerInterface $messenger
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function execute(\Amasty\MessengerWidget\Api\Data\MessengerInterface $messenger): bool;
}
