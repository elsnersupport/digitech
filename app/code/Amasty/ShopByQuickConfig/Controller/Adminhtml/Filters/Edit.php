<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Controller\Adminhtml\Filters;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Edit extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_ShopByQuickConfig::navigation_attributes';

    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);

        if ($this->getRequest()->getParam('attribute_code')) {
            $resultLayout->addHandle(['popup', 'amasty_shopbyconfig_filters_edit_attribute']);
        } else {
            $resultLayout->addHandle(['popup', 'amasty_shopbyconfig_filters_edit_setting']);
        }

        return $resultLayout;
    }
}
