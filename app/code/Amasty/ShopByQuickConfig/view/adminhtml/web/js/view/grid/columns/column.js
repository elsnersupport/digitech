define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Amasty_ShopByQuickConfig/grid/cells/text'
        },

        /**
         * @param {Object} record
         * @returns {Boolean}
         */
        isCustomFilter: function (record) {
            return Boolean(+record[this.is_custom_filter]);
        }
    });
});
