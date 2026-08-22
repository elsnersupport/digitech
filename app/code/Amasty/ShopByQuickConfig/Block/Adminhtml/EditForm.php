<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Block\Adminhtml;

class EditForm extends \Magento\Backend\Block\Widget\Form\Container
{
    protected function _construct()
    {
        parent::_construct();
        $this->removeButton('delete');
        $this->removeButton('reset');
        $this->removeButton('back');

        $this->addButton(
            'cancel',
            [
                'label' => __('Cancel'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'deleteContent', 'target' => '#edit_form']]
                ],
                'class' => 'back'
            ],
            -1
        );
    }

    public function getFormActionUrl()
    {
        $params = [
            'filter_code' => $this->getRequest()->getParam('filter_code')
        ];
        if ($this->getRequest()->getParam('attribute_code')) {
            $params['attribute_code'] = $this->getRequest()->getParam('attribute_code');
        }
        if ($this->getRequest()->getParam('attribute_id')) {
            $params['attribute_id'] = $this->getRequest()->getParam('attribute_id');
        }

        return $this->getUrl('*/*/saveform', $params);
    }
}
