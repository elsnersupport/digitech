/**
 * Filters form component.
 */
define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/form',
    'mageUtils',
    'Amasty_ShopByQuickConfig/js/action/confirm-cancel',
    'Amasty_ShopByQuickConfig/js/model/form-state'
], function ($, _, Form, utils, confirm, formState) {
    'use strict';

    return Form.extend({
        defaults: {
            listens: {
                'responseData': 'onResponseData',
                '${ $.provider }:${ $.dataScope }': 'onDataUpdate'
            },
            modules: {
                form: 'index = filter_edit_form'
            }
        },

        isFormWaiting: false,

        initialize: function () {
            this.save = _.debounce(this.save.bind(this), 25);
            this.saveProxy = _.debounce(this.saveProxy.bind(this), 25);

            this._super();

            return this;
        },

        onDataUpdate: function () {
            if (this.source.get('params.isSaveAvailable')) {
                this.saveProxy();
            }
        },

        saveProxy: function () {
            if (this.source.isCurrentFilterEdited()) {
                confirm({ valid: formState.isFormModified() }).done(function () {
                    this.save();
                    this.form(function (component) {
                        component.destroyInserted();
                    });
                    this.isFormWaiting = true;
                }.bind(this)).fail(function () {
                    this.source.trigger('data.reset');
                }.bind(this));
            } else {
                this.save();
            }
        },

        /**
         * Process Response data.
         * @param {QuickConfigResponseMessages} data
         * @returns {void}
         */
        onResponseData: function (data) {
            this.handleMessages(data);

            if (!data.error && !data.messages) {
                this.source.set('params.isSaveAvailable', false);
                this.source.updateConfig(true, { 'data': data }, { 'data': this.source.get('data') });
                this.source.set('params.isSaveAvailable', true);
            } else if (data.data) {
                this.source.set('params.isSaveAvailable', false);
                this.source.updateConfig(true, { 'data': JSON.parse(data.data) }, { 'data': this.source.get('data') });
                this.source.set('params.isSaveAvailable', true);
            }

            if (this.isFormWaiting) {
                this.isFormWaiting = false;
                this.form(function (component) {
                    component.render();
                });
            }
        },

        /**
         * @param {QuickConfigResponseMessages} data
         * @returns {void}
         */
        handleMessages: function (data) {
            if (data.error) {
                this._resolveMessageSet(data.error, 'error');
                this._resolveMessageSet(data.message, 'error');
                this._resolveMessageSet(data.messages, 'error');
            } else {
                this._resolveMessageSet(data.messages, 'success');
                this._resolveMessageSet(data.message, 'success');
            }
        },

        /**
         * @param {String|QuickConfigMessage[]|undefined} message
         * @param {String} type
         * @return {void}
         * @private
         */
        _resolveMessageSet: function (message, type) {
            if (_.isString(message)) {
                this.source.trigger('addMessage', message, type);
            } else if (_.isArray(message) || _.isObject(message)) {
                _.each(message, function (item) {
                    if (_.isObject(item)) {
                        this.source.trigger('addMessage', item.message, item.type, item);
                    } else {
                        this.source.trigger('addMessage', item, type);
                    }
                }, this);
            }
        },

        /**
         * Updates data from server.
         * @return {void}
         */
        reload: function () {
            var params = _.extend(
                {},
                this.params,
                {
                    ajaxSave: this.ajaxSave,
                    ajaxSaveType: this.ajaxSaveType,
                    response: {
                        data: this.responseData,
                        status: this.responseStatus
                    },
                    attributes: {
                        id: this.namespace
                    }
                }
            );

            utils.ajaxSubmit({
                url: this.reloadUrl,
                data: []
            }, params);
        }
    });
});
