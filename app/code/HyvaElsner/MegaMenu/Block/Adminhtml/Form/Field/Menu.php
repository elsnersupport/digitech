<?php

namespace HyvaElsner\MegaMenu\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Ranges
 */
class Menu extends AbstractFieldArray
{
    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('label', ['label' => __('URL Label'), 'class' => '', 'style' => 'width:120px;']);
        $this->addColumn('url', ['label' => __('Front URL'), 'class' => '', 'style' => 'width:120px;']);
        $this->addColumn('htmlClass', ['label' => __('CSS Class'), 'class' => '', 'style' => 'width:120px;']);
        $this->addColumn('svgName', ['label' => __('SVG Name'), 'class' => '', 'style' => 'width:120px;']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);
        $script = "<script>
                document.addEventListener('DOMContentLoaded', function(event) {
                    require([
                        'jquery',
                        'Magento_Theme/js/sortable'
                    ], function ($) {
                        setTimeout(function () {
                            $('#hyva_mega_menu_menu_configuration_data').sortable({
                                containment: 'parent',
                                items: 'tr',
                                tolerance: 'pointer',
                            });
                        }, 1000);
                    });
                });
            </script>";
        $html .= $script;
        return $html;
    }
}
