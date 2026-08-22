/**
 * Messages component
 */
define([
    'ko',
    'jquery',
    'underscore',
    'mageUtils',
    'uiElement'
], function (ko, $, _, utils, Element) {
    'use strict';

    /**
     * @typedef {Object} MessageItem
     * @property {Number} uid
     * @property {Array.<String>} classes
     * @property {String} clickAction
     * @property {String} message
     * @property {Number} visibilityTimer
     * @property {Number} animationTimer
     */

    return Element.extend({
        defaults: {
            template: 'Amasty_ShopByQuickConfig/messages',
            typesProperties: {
                default: {
                    visibilityTimer: 3000,
                    animationTimer: 3000,
                    classes: {},
                    clickAction: 'removeMessage'
                },
                success: {
                    visibilityTimer: 3000,
                    animationTimer: 3000,
                    classes: {
                        'message-success': true,
                        'success': true
                    },
                    clickAction: 'removeMessage'
                },
                error: {
                    visibilityTimer: 5000,
                    animationTimer: 4000,
                    classes: {
                        'message-error': true,
                        'error': true
                    }
                },
                notice: {
                    visibilityTimer: 4000,
                    animationTimer: 4000,
                    classes: {
                        'message-notice': true,
                        'notice': true
                    },
                    clickAction: 'removeMessage'
                },
                warning: {
                    visibilityTimer: 4000,
                    animationTimer: 4000,
                    classes: {
                        'message-warning': true,
                        'warning': true
                    },
                    clickAction: 'removeMessage'
                }
            }
        },

        defaultType: 'default',

        isVisible: function () {
            // Dummy, gonna be replaced with pureComputed function
        },

        /**
         * @return {Boolean}
         * @private
         */
        _visibleComputedCallback: function () {
            return !!this.messageList().length;
        },

        /**
         * Observable array of message object items
         *
         * @return {Array.<MessageItem>}
         */
        messageList: function () {
            // Dummy, gonna be replaced with ObservableArray function
        },

        initObservable: function () {
            this._super();

            this.messageList = ko.observableArray([]);
            this.isVisible = ko.pureComputed(this._visibleComputedCallback, this);

            return this;
        },

        initLinks: function () {
            this._super();

            this.source.on('addMessage', this.addMessage.bind(this));

            return this;
        },

        /**
         * @param {String} message
         * @param {String|undefined} messageType optional
         * @param {MessageItem|Object|undefined} properties optional
         * @return {void}
         */
        addMessage: function (message, messageType, properties) {
            var type = this.typesProperties[messageType] ? messageType : this.defaultType,
                messageItem = $.extend({}, properties, this.typesProperties[type]),
                uid = utils.uniqueid();

            messageItem.uid = uid;
            messageItem.message = message;

            this.messageList.push(messageItem);

            setTimeout(this.startAnimation.bind(this, uid), messageItem.visibilityTimer);
        },

        /**
         * Animate message removal
         *
         * @param {String} uid
         * @return {void}
         */
        startAnimation: function (uid) {
            var item = this.getMessage(uid);

            if (!item) {
                return;
            }

            $('#' + uid).fadeOut(item.animationTimer, this.removeMessage.bind(this, uid));
        },

        /**
         * @param {String} uid
         * @return {MessageItem|undefined}
         */
        getMessage: function (uid) {
            return _.find(this.messageList(), { uid: uid });
        },

        /**
         * Delete message from message list
         *
         * @param {String} uid
         * @return {void}
         */
        removeMessage: function (uid) {
            this.messageList.remove(function (item) {
                return item.uid === uid;
            });
        },

        /**
         * Process message click action
         *
         * @param {String} uid
         * @return {void}
         */
        handleClick: function (uid) {
            var item = this.getMessage(uid);

            if (!item || !item.clickAction) {
                return;
            }

            this[item.clickAction].call(this, uid);
        }
    });
});
