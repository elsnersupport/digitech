<?php
namespace Meetanshi\OrderTracking\Controller\Adminhtml\Carrier;

use Meetanshi\OrderTracking\Controller\Adminhtml\Carrier;

/**
 * Class Index
 * @package Meetanshi\OrderTracking\Controller\Adminhtml\Carrier
 */
class Index extends Carrier
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $pageResult = $this->resultPageFactory->create();
        $pageResult->getConfig()->getTitle()->prepend(__('Manage Tracking Carriers'));
        return $pageResult;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
