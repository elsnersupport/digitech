/**
 * Insert custom form component.
 */
define([
    'underscore',
    'Magento_Ui/js/form/components/insert',
    'mage/apply/main'
], function (_, Insert, mage) {
    'use strict';

    return Insert.extend({
        defaults: {
            contentSelector: 'amshopbyconfig-filter-edit-form-insert'
        },
        onRender: function () {
            this._super();

            mage.apply();
        }
    });
});
