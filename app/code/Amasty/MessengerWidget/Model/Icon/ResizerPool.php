<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\Icon;

class ResizerPool implements ResizerInterface
{
    /**
     * @var ResizerInterface[]
     */
    private $resizers;

    public function __construct(
        array $resizers = []
    ) {
        $this->resizers = $resizers;
    }

    /**
     * @param string $file
     * @throws \InvalidArgumentException
     */
    public function execute(string $file): void
    {
        foreach ($this->resizers as $resizer) {
            if ($resizer instanceof ResizerInterface) {
                $resizer->execute($file);
            } else {
                throw new \InvalidArgumentException(
                    'Type "' . get_class($resizer) . '" is not instance on ' . ResizerInterface::class
                );
            }
        }
    }
}
