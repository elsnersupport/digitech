<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Controller\Adminhtml\Icons;

use Amasty\MessengerWidget\Model\Icon\Uploader;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\UrlInterface;

class Upload extends Action implements HttpPostActionInterface
{
    public const REQUEST_PARAMETER = 'param_name';

    /**
     * @var Uploader
     */
    private $uploader;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    public function __construct(
        Context $context,
        Uploader $uploader,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->uploader = $uploader;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        if ($this->getRequest()->getParam(self::REQUEST_PARAMETER)) {
            $result = $this->uploader->uploadImage(
                $this->getRequest()->getParam(self::REQUEST_PARAMETER),
                $this->_backendUrl->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA])
            );
        }

        return $this->resultJsonFactory->create()->setData($result);
    }
}
