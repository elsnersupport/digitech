/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/action/set-coupon-code',
    'Magento_SalesRule/js/action/cancel-coupon',
    'Magento_SalesRule/js/model/coupon'
], function ($, ko, Component, quote, setCouponCodeAction, cancelCouponAction, coupon) {
    'use strict';

    var totals = quote.getTotals(),
        couponCode = coupon.getCouponCode(),
        isApplied = coupon.getIsApplied();

    if (totals()) {
        couponCode(totals()['coupon_code']);
    }
    isApplied(couponCode() != null);

    return Component.extend({
        defaults: {
            template: 'Magento_SalesRule/payment/discount'
        },
        couponCode: couponCode,

        /**
         * Applied flag
         */
        isApplied: isApplied,

        initialize: function () {
            this._super();
            var self = this;
            if (self.isApplied()) {
                let x = window.setInterval(() => {
                    if ($('.current-coupon-code').length) {
                        window.clearInterval(x);
                        $('.current-coupon-code').html(self.couponCode());
                    }
                }, 250);
            }
            self.isApplied.subscribe(value => {
                var code = "Use coupon code";
                if (value) {
                    code = self.couponCode();
                }
                let x = window.setInterval(() => {
                    if ($('.current-coupon-code').length) {
                        window.clearInterval(x);
                        $('.current-coupon-code').html(code);
                    }
                }, 250);
                $('#coupon-code-popup').modal('closeModal');
            })
        },
        /**
         * Coupon code application procedure
         */
        apply: function () {
            if (this.validate()) {
                setCouponCodeAction(couponCode(), isApplied);
            }
        },

        /**
         * Cancel using coupon
         */
        cancel: function () {
            if (this.validate()) {
                couponCode('');
                cancelCouponAction(isApplied);
            }
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            let form = '#discount-form';

            $(form + ' input[type="text"]').each(function () {
                let currentValue = $(this).val();

                $(this).val(currentValue.trim());
            });
            return $(form).validation() && $(form).validation('isValid');
        }
    });
});
