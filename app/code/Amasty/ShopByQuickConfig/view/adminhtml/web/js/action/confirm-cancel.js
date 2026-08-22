/**
 * Show confirmation window before delete form.
 */
define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Ui/js/modal/confirm',
    'mage/template',
    'text!Amasty_ShopByQuickConfig/template/confirm-cancel.html',
    'mage/translate'
], function ($, _, ko, confirm, templateRender, confirmTemplate) {
    'use strict';

    var doNotShowAgain = ko.observable(false),

        /**
         * @param {Object} inputConfig
         * @returns {Object}
         */
        resolveConfig = function (inputConfig) {
            var config = inputConfig || {};

            if (_.isUndefined(config.message)) {
                config.message = $.mage.__('You have unsaved changes that will be lost'
                    + ' if you decide to exit the settings area.');
            }

            if (_.isUndefined(config.label)) {
                config.label = $.mage.__('Do not display this warning again.');
            }

            if (_.isUndefined(config.title)) {
                config.title = $.mage.__('Are you sure?');
            }

            if (_.isUndefined(config.observableValue) || !ko.isObservable(config.observableValue)) {
                config.observableValue = doNotShowAgain;
            }

            if (_.isUndefined(config.valid)) {
                config.valid = true;
            }

            return config;
        },

        /**
         * @param {Object} inputConfig
         * @returns {Deferred}
         */
        mainFunction = function (inputConfig) {
            var deferred = $.Deferred(),
                config = resolveConfig(inputConfig),
                content;

            if (config.observableValue() || !config.valid) {
                deferred.resolve();

                return deferred.promise();
            }

            content = templateRender(
                confirmTemplate,
                config
            );

            confirm({
                title: config.title,
                content: content,
                actions: {
                    confirm: function () {
                        config.observableValue($('#amshopbyconfig-dont-show-confirm-checkbox').is(':checked'));
                        deferred.resolve();
                    },
                    cancel: function () {
                        deferred.reject();
                    }
                }
            });

            return deferred.promise();
        };

    return mainFunction;
});
