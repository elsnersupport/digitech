/**
 * Generic form actions.
 */
define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Amasty_ShopByQuickConfig/js/action/confirm-cancel',
    './active-filter',
    './form-state',
    'mage/translate'
], function ($, _, registry, confirm, activeFilter, formState) {
    'use strict';

    /**
     * @typedef {Object} QuickConfigResponseMessages
     * @property {QuickConfigMessage[]} messages
     * @property {undefined|String} error
     * @property {undefined|*} data
     */
    /**
     * @typedef {Object} QuickConfigMessage
     * @property {String} message message body
     * @property {String} type error|success|warning|notice
     */

    var formActions = {
        options: {
            formSelector: '#edit_form',
            uiFormName: 'amasty_shopby_filters.amasty_shopby_filters',
            uiInsertFormName: 'index = filter_edit_form',
            uiFormPlaceholderName: 'index = catalog_placeholder'
        },

        /**
         * @returns {Boolean}
         */
        isFormVisible: function () {
            return !!$(this.options.formSelector).length;
        },

        /**
         * @returns {Object}
         */
        getUiForm: function () {
            return registry.get(this.options.uiFormName);
        },

        /**
         * @returns {Deferred}
         */
        cancelAction: function () {
            var promise = confirm({ valid: formState.isFormModified() });

            promise.done(this.removeForm);

            return promise;
        },

        /**
         * Remove edit area.
         *
         * @returns {void}
         */
        removeForm: function () {
            var insert = registry.get(this.options.uiInsertFormName),
                placeholder = registry.get(this.options.uiFormPlaceholderName);

            activeFilter.activeFilterCode('');
            formState.resetState();
            insert.destroyInserted();
            placeholder.visible(true);
        },

        /**
         * @returns {void}
         */
        reloadForm: function () {
            var insert = registry.get(this.options.uiInsertFormName);

            formState.resetState();
            insert.destroyInserted();
            insert.render();
        },

        /**
         * @param {HTMLFormElement} form
         * @param {jQuery.Event} event
         * @returns {void}
         */
        formSubmitHandle: function (form, event) {
            var $form = $(form),
                $body = $('body');

            event.preventDefault();

            $body.trigger('processStart');

            return $.ajax({
                type: $form.attr('method'),
                url: $form.attr('action'),
                data: $form.serialize(),
                global: true
            })
                .done(this.onSuccess)
                .fail(this.onError)
                .always(function () {
                    $body.trigger('processStop');
                });
        },

        /**
         * @param {QuickConfigResponseMessages} data
         * @returns {void}
         */
        onSuccess: function (data) {
            var form = this.getUiForm();

            form.responseStatus(null);
            form.responseStatus(!data.error);

            if (data) {
                form.responseData(data);
            }

            form.reload();
            this.reloadForm();
        },

        /**
         * Prepare form for error state
         *
         * @returns {void}
         */
        onError: function () {
            var form = this.getUiForm();

            form.responseStatus(null);
            form.responseStatus(false);
            form.responseData({
                error: true,
                messages: 'Something went wrong.'
            });
        }
    };

    _.bindAll(
        formActions,
        'cancelAction',
        'removeForm',
        'onSuccess',
        'onError',
        'formSubmitHandle',
        'isFormVisible'
    );

    return formActions;
});
