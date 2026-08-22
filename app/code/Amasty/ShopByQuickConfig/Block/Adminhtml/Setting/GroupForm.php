<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Block\Adminhtml\Setting;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form;
use Magento\Config\Model\Config\Factory;
use Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Section;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

/**
 * System Configuration Form with one group
 */
class GroupForm extends Form
{
    /**
     * @var FieldsetFactory
     */
    private $filterFieldset;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Factory $configFactory
     * @param Structure $configStructure
     * @param Form\Fieldset\Factory $fieldsetFactory
     * @param Form\Field\Factory $fieldFactory
     * @param SettingChecker $settingChecker
     * @param FieldsetFactory $filterFieldset
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Factory $configFactory,
        Structure $configStructure,
        Form\Fieldset\Factory $fieldsetFactory,
        Form\Field\Factory $fieldFactory,
        SettingChecker $settingChecker,
        FieldsetFactory $filterFieldset,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $configFactory,
            $configStructure,
            $fieldsetFactory,
            $fieldFactory,
            $data,
            $settingChecker
        );
        $this->filterFieldset = $filterFieldset;
    }

    public function initForm()
    {
        $this->_initObjects();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        /** @var $section Section */
        $section = $this->_configStructure->getElement($this->getSectionCode());
        if ($section && $section->isVisible($this->getWebsiteCode(), $this->getStoreCode())) {
            foreach ($section->getChildren() as $group) {
                if ($group->getId() === $this->getRequest()->getParam('filter_code', '') . '_filter') {
                    $this->_initGroup($group, $section, $form);
                    break;
                }
            }
        }

        $this->setForm($form);

        return $this;
    }

    /**
     * @param Structure\Element\Field $field
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getScopeLabel(\Magento\Config\Model\Config\Structure\Element\Field $field)
    {
        return '';
    }

    protected function _initObjects()
    {
        parent::_initObjects();
        $this->_fieldsetRenderer = $this->filterFieldset->create();

        return $this;
    }

    public function getSectionCode()
    {
        return \Amasty\Shopby\Model\UrlBuilder\Adapter::SELF_MODULE_NAME;
    }

    public function getWebsiteCode()
    {
        return '';
    }

    public function getStoreCode()
    {
        return '';
    }
}
