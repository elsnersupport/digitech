<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\Messenger;

use Amasty\MessengerWidget\Api\Data\MessengerInterface;
use Amasty\MessengerWidget\Api\MessengerDeleteInterface;
use Amasty\MessengerWidget\Model\ResourceModel\Messenger;
use Magento\Framework\Exception\CouldNotDeleteException;

class MessengerDelete implements MessengerDeleteInterface
{
    /**
     * @var Messenger
     */
    private $resource;

    public function __construct(Messenger $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param MessengerInterface $messenger
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function execute(MessengerInterface $messenger): bool
    {
        try {
            $this->resource->delete($messenger);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }
}
