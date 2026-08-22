<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Model\Icon;

use Amasty\MessengerWidget\Model\Config\Source\MessengerCode;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Uploader as UploaderFramework;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\StoreManagerInterface;

class Uploader
{
    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ResizerPool
     */
    private $resizer;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array|string[]
     */
    private $allowedMimeTypes;

    /**
     * @var array|string[]
     */
    private $allowedFileExtensions;

    /**
     * @var Repository
     */
    private $assetRepository;

    public function __construct(
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        ResizerPool $resizer,
        StoreManagerInterface $storeManager,
        Repository $assetRepository,
        array $allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpeg', 'image/svg+xml', 'image/svg'],
        array $allowedFileExtensions = ['jpeg', 'jpg', 'png', 'svg']
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->resizer = $resizer;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->storeManager = $storeManager;
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->allowedFileExtensions = $allowedFileExtensions;
        $this->assetRepository = $assetRepository;
    }

    /**
     * Allow users to upload images to the folder structure
     *
     * @param string $fieldName
     * @param string $baseUrl
     * @return array
     */
    public function uploadImage(string $fieldName, string $baseUrl): array
    {
        $fileUploader = $this->initializeFileUploader($fieldName);

        try {
            if (!$fileUploader->checkMimeType($this->allowedMimeTypes)) {
                throw new \Magento\Framework\Exception\FileSystemException(__('Please use .png .jpg .svg images.'));
            }

            $result = $fileUploader->save(
                $this->getDirectoryObject()->getAbsolutePath(
                    ResizerInterface::UPLOAD_DIR
                )
            );
            $result['url'] = $baseUrl .
                $this->getFilePath(ResizerInterface::UPLOAD_DIR, $result['file']);
            if ($fileUploader->getFileExtension() !== 'svg') {
                $this->resizer->execute($fileUploader->getUploadedFileName());
            }
        } catch (\Exception $e) {
            $this->remove($fileUploader->getUploadedFileName());
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }

        return $result;
    }

    /**
     * @param string $fieldName
     * @return UploaderFramework
     */
    private function initializeFileUploader(string $fieldName): UploaderFramework
    {
        $fileUploader = $this->uploaderFactory->create(['fileId' => $fieldName]);
        $fileUploader->setFilesDispersion(false);
        $fileUploader->setAllowRenameFiles(true);
        $fileUploader->setAllowedExtensions($this->allowedFileExtensions);
        $fileUploader->setAllowCreateFolders(true);

        return $fileUploader;
    }

    /**
     * @return WriteInterface
     */
    private function getDirectoryObject(): WriteInterface
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * @param $filename
     */
    public function remove($filename): void
    {
        $this->getDirectoryObject()->delete(
            $this->getDirectoryObject()->getAbsolutePath(ResizerInterface::UPLOAD_DIR . '/' . $filename)
        );

        $this->getDirectoryObject()->delete(
            $this->getDirectoryObject()->getAbsolutePath(ResizerInterface::UPLOAD_DIR_RETINA . '/' . $filename)
        );
    }

    /**
     * @param string $path
     * @param string $imageName
     * @return string
     */
    public function getFilePath(string $path, string $imageName): string
    {
        return rtrim($path, '/') . '/' . ltrim($imageName, '/');
    }

    /**
     * @return string
     */
    public function getAbsolutePathToDefaultImages(): string
    {
        $fileId = 'Amasty_MessengerWidget::images/';
        $params = [
            'area' => 'frontend'
        ];
        $asset = $this->assetRepository->createAsset($fileId, $params);

        return $asset->getUrl();
    }

    /**
     * Retrieve file info
     *
     * @param string $fileName
     * @return array
     */
    public function getFileInfo(string $fileName): array
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $filePath = $this->getFilePath(ResizerInterface::UPLOAD_DIR, $fileName);
        $filePathRetina = $this->getFilePath(ResizerInterface::UPLOAD_DIR_RETINA, $fileName);
        $fileInfo = [];

        if ($this->mediaDirectory->isFile($filePath)) {
            $stat = $this->mediaDirectory->stat($filePath);
            $fileAbsolutePath = $this->storeManager
                    ->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $filePath;
            $fileInfo = [
                'name' => basename($fileName),
                'file_path' => $filePath,
                'file' => $fileName,
                'type' => 'image',
                'extension' => pathinfo($fileAbsolutePath)['extension'],
                'size' => $stat['size'] ?? 0,
                'url' => $fileAbsolutePath,
                'retinaUrl' => $this->storeManager
                    ->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $filePathRetina,
            ];
        }

        return $fileInfo;
    }
}
