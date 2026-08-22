<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\ResourceModel\Messenger\Grid\Collection\DataFiller;

use Amasty\MessengerWidget\Api\Data\MessengerInterface;
use Amasty\MessengerWidget\Model\Config\Source\MessengerCode;
use Amasty\MessengerWidget\Model\ResourceModel\Collection\DataFillerInterface;
use Amasty\MessengerWidget\Model\ResourceModel\Messenger\Grid\Collection;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class MessengerName implements DataFillerInterface
{
    /**
     * @var MessengerCode
     */
    private $messengerCode;

    public function __construct(MessengerCode $messengerCode)
    {
        $this->messengerCode = $messengerCode;
    }

    /**
     * @param AbstractCollection|Collection $collection
     * @return void
     */
    public function attachData(AbstractCollection $collection): void
    {
        $labelsHash = $this->getMessengerLabelsHash();
        foreach ($collection->getItems() as $item) {

            $messengerCode = $item[MessengerInterface::CODE] ?? '';
            $messengerName = $labelsHash[$messengerCode] ?? '';
            if ($messengerCode === MessengerCode::OTHER) {
                $messengerName = $item[MessengerInterface::CUSTOM_NAME];
            }

            $item->setData('name', $messengerName);
        }
    }

    /**
     * @return string[] array(messengerKey => messengerLabel)
     */
    private function getMessengerLabelsHash(): array
    {
        $labels = [];

        foreach ($this->messengerCode->toOptionArray() as $item) {
            $labels[$item['value']] = $item['label'];
        }

        return $labels;
    }
}
