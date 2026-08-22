/**
 * Dynamic rows record component
 */
define([
    'ko',
    'underscore',
    'Magento_Ui/js/dynamic-rows/record',
    'Amasty_ShopByQuickConfig/js/model/active-filter'
], function (ko, _, Record, activeFilter) {
    'use strict';

    return Record.extend({
        initObservable: function () {
            this._super();

            this.isActive = ko.pureComputed(this._isActiveComputedCallback, this);

            return this;
        },

        _isActiveComputedCallback: function () {
            return this.data().filter_code === activeFilter.activeFilterCode();
        }
    });
});
