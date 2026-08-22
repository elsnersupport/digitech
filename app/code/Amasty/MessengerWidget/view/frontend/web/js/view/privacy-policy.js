/**
 * Privacy Policy Popup Component
 */

define(['jquery', 'ko', 'uiComponent', 'mage/cookies'], function ($, ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            isPopupOpened: false,
            isPrivacyPolicyAccepted: false,
            privacyPolicyContent: '',
            isPrivacyPolicyEnabled: false,
            links: {
                // eslint-disable-next-line no-template-curly-in-string
                'isPopupOpened': '${ $.parentName }:isPrivacyPopupOpened',
                // eslint-disable-next-line no-template-curly-in-string
                'isPrivacyPolicyAccepted': '${ $.parentName }:isPrivacyPolicyAccepted'
            },
            cookieKey: 'ammessenger-privacy-policy-accepted'
        },

        initialize: function () {
            this._super();

            if (!this.isPrivacyPolicyEnabled) {
                this.isPrivacyPolicyAccepted(true);

                return this;
            }

            this.isPrivacyPolicyAccepted(this.getPrivacyPolicyAcceptState());

            $(document).keydown(function (event) {
                if (event.keyCode === 27) {
                    this.closePopup();
                }
            }.bind(this));

            return this;
        },

        initObservable: function () {
            this._super().observe(['isPopupOpened', 'isPrivacyPolicyAccepted', 'isEnabled']);

            return this;
        },

        openPopup: function () {
            this.isPopupOpened(true);
        },

        closePopup: function () {
            this.isPopupOpened(false);
        },

        acceptPrivacyPolicy: function () {
            var currentDate = new Date();

            $.mage.cookies.set(
                this.cookieKey,
                true,
                {
                    expires: new Date(currentDate.setDate(currentDate.getDate() + 365))
                }
            );

            this.isPrivacyPolicyAccepted(true);
            this.closePopup();
        },

        getPrivacyPolicyAcceptState: function () {
            return $.mage.cookies.get(this.cookieKey);
        }
    });
});
