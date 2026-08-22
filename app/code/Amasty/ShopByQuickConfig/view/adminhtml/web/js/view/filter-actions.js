/**
 * Filter items button.
 * Set query params for insert form.
 */
define([
    'jquery',
    'Magento_Ui/js/form/components/button',
    'uiRegistry',
    'Amasty_ShopByQuickConfig/js/action/confirm-cancel',
    'Amasty_ShopByQuickConfig/js/model/form-actions',
    'Amasty_ShopByQuickConfig/js/model/active-filter',
    'Amasty_ShopByQuickConfig/js/model/form-state'
], function ($, Element, registry, confirm, formActions, activeFilter, formState) {
    'use strict';

    return Element.extend({
        action: function () {
            if ($('#edit_form').length) {
                if (this.isCurrentFormAction()) {
                    return;
                }

                confirm({ valid: formState.isFormModified() }).done(
                    function () {
                        formActions.removeForm();
                        this.action();
                    }.bind(this)
                );

                return;
            }

            this.updateParams();
            this._super();
        },

        /**
         * @return {boolean}
         */
        isCurrentFormAction: function () {
            var filterCode = this.source.get(this.dataScope).filter_code;

            return activeFilter.activeFilterCode() === filterCode;
        },

        updateParams: function () {
            var record = this.source.get(this.dataScope),
                queryParams = {
                    filter_code: record.filter_code
                };

            if (record.attribute_code) {
                queryParams.attribute_code = record.attribute_code;
            }

            activeFilter.activeFilterCode(record.filter_code);

            registry.get(this.formName).set('params', queryParams);
        }
    });
});
