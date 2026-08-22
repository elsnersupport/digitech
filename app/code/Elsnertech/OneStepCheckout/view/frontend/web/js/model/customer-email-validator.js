/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Customer/js/model/customer',
    'mage/validation'
], function ($, customer) {
    'use strict';

    return {
        /**
         * Validate checkout agreements
         *
         * @returns {Boolean}
         */
        validate: function (hideErrorMessage = true) {
            var emailValidationResult = customer.isLoggedIn(),
                loginFormSelector = 'form[data-role=email-with-possible-login]';

            if (!customer.isLoggedIn()) {
                $(loginFormSelector).validation();
                emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                if (!emailValidationResult) {
                    if (hideErrorMessage) {
                        $('form.form.form-login').find('#customer-email-error').hide();
                        $('form.form.form-login').find('#customer-email').removeClass('mage-error');
                    } else {
                        $('form.form.form-login').find('#customer-email-error').show();
                        $('form.form.form-login').find('#customer-email').addClass('mage-error');
                    }
                }
            }

            return emailValidationResult;
        }
    };
});
