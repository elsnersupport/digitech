/**
 * Modal component with additional form actions
 */
define([
    'jquery',
    'underscore',
    'Magento_Ui/js/modal/modal-component',
    'Amasty_ShopByQuickConfig/js/action/confirm-cancel',
    'Amasty_ShopByQuickConfig/js/model/form-actions',
    'mageUtils'
], function ($, _, Modal, confirm, formActions, utils) {
    'use strict';

    return Modal.extend({
        defaults: {
            saveUrl: '',
            modules: {
                listing: '${ $.name }.add_attribute_listing'
            }
        },

        /**
         * Open modal if form edit closed
         * @returns {void}
         */
        openModal: function () {
            if (formActions.isFormVisible()) {
                formActions.cancelAction().done(this.openModal);
            } else {
                this._super();
                this.elems().forEach(function (element) {
                    element.destroyInserted();
                    element.render();
                }, this);
            }
        },

        /**
         * Show confirm before close if exist not saved data.
         *
         * @returns {void}
         */
        closeModalConfirm: function () {
            var data = this._collectData();

            confirm({ valid: !_.isEmpty(data) }).done(this.closeModal);
        },

        /**
         * Accept changes in modal.
         *
         * @return {void}
         */
        actionDone: function () {
            this.saveSelected().done(this.closeModal.bind(this));
        },

        /**
         * Accept changes in modal and reload modal content.
         *
         * @return {void}
         */
        actionSaveAndContinue: function () {
            this.saveSelected().done(function () {
                this.elems().forEach(function (element) {
                    element.destroyInserted();
                    element.render();
                }, this);
            }.bind(this));
        },

        /**
         * @param {Object|Undefined} data
         * @returns {void}
         */
        responseHandler: function (data) {
            var form = formActions.getUiForm();

            form.responseStatus(null);

            if (data) {
                form.responseStatus(!data.error);
                form.responseData(data);
            }

            form.reload();
        },

        /**
         * Send listing selected data to server.
         * @returns {Deferred}
         */
        saveSelected: function () {
            var data = this._collectData(),
                deferred,
                params = _.extend(
                    {},
                    this.params,
                    {
                        ajaxSave: true,
                        ajaxSaveType: 'simple'
                    }
                );

            if (_.isEmpty(data)) {
                deferred = $.Deferred().resolve();

                return deferred.promise();
            }

            return utils.ajaxSubmit({
                url: this.saveUrl,
                data: data
            }, params).done(this.responseHandler.bind(this));
        },

        /**
         * Return data for save action.
         *
         * @returns {Object}
         * @protected
         */
        _collectData: function () {
            var data = {},
                selected = this.listing().selections().selected();

            if (!_.isEmpty(selected)) {
                data.selected = selected;
            }

            return data;
        }
    });
});
