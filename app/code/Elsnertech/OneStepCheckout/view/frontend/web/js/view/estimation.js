/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/sidebar'
], function ($, Component, quote, priceUtils, totals, sidebarModel) {
    'use strict';

    return Component.extend({
        isLoading: totals.isLoading,
        initialize: function () {
            this._super();
            var self = this;
            window.onscroll = function () {
                if (window.innerWidth <= 768) {
                    if (self.isElementInViewport($('.data.table.table-totals'))) {
                        $('.opc-estimated-wrapper').hide();
                    } else {
                        $('.opc-estimated-wrapper').show();
                    }
                }
            }
        },
        isElementInViewport: function (el) {
            if (el.length) {
                var elementTop = el.offset().top;
                var elementBottom = elementTop + el.outerHeight();

                var viewportTop = $(window).scrollTop();
                var viewportBottom = viewportTop + $(window).height();

                return elementBottom > viewportTop && elementTop < viewportBottom;
            } else {
                return false;
            }

        },
        /**
         * @return {Number}
         */
        getQuantity: function () {
            if (totals.totals()) {
                return parseFloat(totals.totals()['items_qty']);
            }

            return 0;
        },

        /**
         * @return {Number}
         */
        getPureValue: function () {
            if (totals.totals()) {
                return parseFloat(totals.getSegment('grand_total').value);
            }

            return 0;
        },

        /**
         * Show sidebar.
         */
        showSidebar: function () {
            sidebarModel.show();
        },

        /**
         * @param {*} price
         * @return {*|String}
         */
        getFormattedPrice: function (price) {
            return priceUtils.formatPriceLocale(price, quote.getPriceFormat());
        },

        /**
         * @return {*|String}
         */
        getValue: function () {
            return this.getFormattedPrice(this.getPureValue());
        },
        placeOrder: function () {
            window.dispatchEvent(new CustomEvent('place-order'));
        },
        setFocus: function () {
            $('html, body').animate({
                scrollTop: $('.checkout-placeorder').offset().top
            }, 1000);
        }
    });
});
