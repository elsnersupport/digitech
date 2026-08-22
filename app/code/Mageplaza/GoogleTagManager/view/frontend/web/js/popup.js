/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_BetterPopup
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mpgoogletagmanager.popup', {
        options: {
            cookie_validity: '',
        },

        _create: function () {
            if (!this.checkCookie('mageplaza_analytics') && !this.checkCookie('mageplaza-consent-targeting')) {
                this._showPopup();
            }
            this._clickClose();
            this._enableButtonSave();
            this._clickActions();

            if (this.checkCookie('mageplaza-consent-tracking') && this.checkCookie('mageplaza-consent-targeting')) {
                var tracking = this.getCookie('mageplaza-consent-tracking'),
                    targeting = this.getCookie('mageplaza-consent-targeting');
                this.renderConsent(tracking, targeting, 'default');
            } else {
                gtag('consent', 'default', {
                    'analytics_storage': 'denied',
                    'ad_storage': 'denied',
                    'ad_user_data': 'denied',
                    'ad_personalization': 'denied'
                });
            }
        },

        _showPopup: function () {
            var bgEl = $('#bio_ep_bg'),
                popup = $('#consent-popup');

            if (!bgEl.length) {
                $('body').append("<div id='bio_ep_bg'></div>");
                $('#bio_ep_bg').show();
            }
            popup.show();
        },

        _enableButtonSave: function () {
            var buttonSave = $('button.save');

            buttonSave.prop('disabled', true);
            buttonSave.css({"cursor": "not-allowed", "pointer-events": "unset"});
            $('input[type="checkbox"]').change(function() {
                if ($('input[type="checkbox"]:checked').length > 0) {
                    buttonSave.prop('disabled', false);
                    buttonSave.css({"color": "#1979c3", "border": "1px solid #1979c3", "cursor": "pointer", "pointer-events": "auto"});
                } else {
                    buttonSave.prop('disabled', true);
                    buttonSave.css({"color": "#333333", "border": "1px solid #cccccc", "cursor": "not-allowed", "pointer-events": "unset"});
                }
            });
        },

        /**
         * Event click close popup button
         * @private
         */
        _clickClose: function () {
            var self = this;

            $('.close-popup').click(function () {
                self.applyAction(false);
            })
        },

        _clickActions: function () {
            var self = this;

            $('.accept').click(function () {
                self.applyAction(true);
            });

            $('.reject').click(function () {
                self.applyAction(false);
            });

            $('.save').click(function () {
                if ($('#analytics:checked').length > 0) {
                    self.setCookie('mageplaza_analytics', 'true');
                }
                if ($('#advertisement:checked').length > 0) {
                    self.setCookie('mageplaza-consent-tracking', 'true');
                    self.setCookie('mageplaza-consent-targeting', 'true');
                    self.renderConsent(true, true, 'update');
                }
                $('#consent-popup').hide();
                $('#bio_ep_bg').hide();
            });
        },

        applyAction: function (bool) {
            var self = this;

            self.setCookie('mageplaza-consent-tracking', bool);
            self.setCookie('mageplaza-consent-targeting', bool);
            self.setCookie('mageplaza_analytics', bool);
            self.renderConsent(bool, bool, 'update');
            $('#consent-popup').hide();
            $('#bio_ep_bg').hide();
        },

        renderConsent: function (tracking, targeting, type) {
            if (tracking && targeting) {
                gtag('consent', type, {
                    'ad_storage': 'granted',
                    'ad_user_data': 'granted',
                    'ad_personalization': 'granted',
                    'analytics_storage': 'granted'
                });
            }

            if (!tracking && targeting) {
                gtag('consent', type, {
                    'ad_storage': 'denied',
                    'ad_user_data': 'granted',
                    'ad_personalization': 'granted',
                    'analytics_storage': 'denied'
                });
            }

            if (tracking && !targeting) {
                gtag('consent', type, {
                    'ad_storage': 'denied',
                    'ad_user_data': 'granted',
                    'ad_personalization': 'granted',
                    'analytics_storage': 'denied'
                });
            }

            if (!tracking && !targeting) {
                gtag('consent', type, {
                    'ad_storage': 'denied',
                    'ad_user_data': 'denied',
                    'ad_personalization': 'denied',
                    'analytics_storage': 'denied'
                });
            }
        },

        setCookie: function (cookieName, cookieValue) {
            var self = this,
                d = new Date(),
                expireDays = self.options.cookie_validity;

            d.setDate(d.getDate() + expireDays);
            var expires = "expires=" + d.toUTCString();

            document.cookie = cookieName + "=" + cookieValue + ";" + expires + ";path=/";
        },

        getCookie: function  (cookieName) {
            var name = cookieName + "=",
                ca = document.cookie.split(';');

            for (var i = 0; i< ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) === 0) {
                    return c.substring(name.length, c.length);
                }
            }

            return "";
        },

        checkCookie: function (cookieName) {
            var cookieValue = this.getCookie(cookieName);

            return cookieValue !== "";
        },

    });

    return $.mpgoogletagmanager.popup;
});