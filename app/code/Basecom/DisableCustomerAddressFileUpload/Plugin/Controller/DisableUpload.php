<?php
declare(strict_types=1);

namespace Basecom\DisableCustomerAddressFileUpload\Plugin\Controller;

use Magento\Customer\Controller\Address\File\Upload;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class DisableUpload
{
    private ResultFactory $resultFactory;

    public function __construct(ResultFactory $resultFactory)
    {
        $this->resultFactory = $resultFactory;
    }

    /**
     * Prevent file upload for customer address attributes
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(Upload $subject, callable $proceed): ResultInterface
    {
        /** @var Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData([
            'error' => (string)__('File uploads for customer address attributes are disabled.'),
            'errorcode' => 0,
        ]);

        return $result;
    }
}
