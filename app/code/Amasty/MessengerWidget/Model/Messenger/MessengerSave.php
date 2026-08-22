<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\Messenger;

use Amasty\MessengerWidget\Api\Data\MessengerInterface;
use Amasty\MessengerWidget\Api\MessengerSaveInterface;
use Amasty\MessengerWidget\Model\ResourceModel\Messenger;
use Magento\Framework\Exception\CouldNotSaveException;

class MessengerSave implements MessengerSaveInterface
{
    /**
     * @var Messenger
     */
    private $resource;

    public function __construct(Messenger $resource)
    {
        $this->resource = $resource;
    }

    public function execute(MessengerInterface $messenger): MessengerInterface
    {
        try {
            $this->resource->save($messenger);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Problems with saving messenger'), $e);
        }

        return $messenger;
    }
}
