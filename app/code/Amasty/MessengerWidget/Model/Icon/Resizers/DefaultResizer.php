<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\Icon\Resizers;

use Amasty\MessengerWidget\Model\Icon\ResizerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Image\AdapterFactory;

class DefaultResizer implements ResizerInterface
{
    /**
     * @var AdapterFactory
     */
    private $imageAdapterFactory;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var string
     */
    private $destinationDirectory;

    /**
     * @var string
     */
    private $sourceDirectory;

    /**
     * @var int
     */
    private $imageSize;

    public function __construct(
        AdapterFactory $imageAdapterFactory,
        Filesystem $filesystem,
        int $imageSize = 0,
        string $destinationDirectory = '',
        string $sourceDirectory = ''
    ) {
        $this->imageAdapterFactory = $imageAdapterFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->destinationDirectory = $destinationDirectory;
        $this->sourceDirectory = $sourceDirectory;
        $this->imageSize = $imageSize;
    }

    /**
     * @param string $file
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function execute(string $file): void
    {
        $absoluteImagePath = $this->mediaDirectory->getAbsolutePath($this->sourceDirectory . '/' . $file);
        $imageFactory = $this->imageAdapterFactory->create();
        $imageFactory->open($absoluteImagePath);
        $imageWidth = $imageFactory->getOriginalWidth();
        $imageHeight = $imageFactory->getOriginalHeight();
        if ($imageWidth !== $imageHeight) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Please use images that have side ratio 1:1')
            );
        }

        if (($imageWidth < self::MINIMUM_UPLOAD_SIZE) || ($imageHeight < self::MINIMUM_UPLOAD_SIZE)) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Please use images that have minimal resolution of 120px x 120px.')
            );
        }
        $imageFactory->resize($this->imageSize, $this->imageSize);
        $imageFactory->save($this->mediaDirectory->getAbsolutePath($this->destinationDirectory), $file);
    }
}
