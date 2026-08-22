/**
 * Default messenger icon view
 */
define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/abstract'
], function ($, _, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            imageMap: {},
            altText: ''
        },

        initialize: function () {
            this._super().observe('altText');
        }
    });
});
