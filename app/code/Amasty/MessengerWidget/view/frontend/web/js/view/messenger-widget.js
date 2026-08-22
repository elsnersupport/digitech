/**
 * Messengers Widget Component
 */

define(['ko', 'uiComponent'], function (ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            widgetPosition: '',
            widgetPositionCss: '-bottom -left',
            widgetCssModifiers: '',
            openedState: false,
            isPrivacyPopupOpened: false,
            isPrivacyPolicyAccepted: false,
            cssModifiers: {
                openedState: '-opened',
                maxCircleMessengers: 5,
                circleOpenedView: '-drop-circle',
                lineOpenedView: '-drop-line'
            },
            messengers: [],
            imports: {
                isPrivacyPolicyEnabled:
                    // eslint-disable-next-line no-template-curly-in-string
                    '${ $.name }.ammessenger-privacy-policy:isPrivacyPolicyEnabled'
            }
        },

        initialize: function () {
            this._super();

            this.setWidgetPosition();

            return this;
        },

        initObservable: function () {
            this._super().observe([
                'widgetPositionCss',
                'openedState',
                'isPrivacyPopupOpened',
                'isPrivacyPolicyAccepted'
            ]);

            this.widgetCssModifiers = ko.pureComputed(function () {
                var openedStatus = this.openedState() ? ' ' + this.cssModifiers.openedState : '';

                return this.getWidgetCssModifiers() + openedStatus;
            }, this);

            return this;
        },

        setWidgetPosition: function () {
            switch (this.widgetPosition) {
                case 'top-left':
                    this.widgetPositionCss('-top -left');
                    break;
                case 'top-right':
                    this.widgetPositionCss('-top -right');
                    break;
                case 'center-left':
                    this.widgetPositionCss('-center -left');
                    break;
                case 'center-right':
                    this.widgetPositionCss('-center -right');
                    break;
                case 'bottom-left':
                    this.widgetPositionCss('-bottom -left');
                    break;
                case 'bottom-right':
                    this.widgetPositionCss('-bottom -right');
                    break;
                default:
                    this.widgetPositionCss('-bottom -right');
            }
        },

        onMainButtonClick: function () {
            if (this.isPrivacyPolicyEnabled && !this.isPrivacyPolicyAccepted()) {
                this.isPrivacyPopupOpened(true);

                return;
            }

            if (this.messengers.length > 1) {
                this.openedState(!this.openedState());
            }
        },

        onMessengerClick: function () {
            // eslint-disable-next-line no-restricted-globals
            event.stopPropagation();

            return true;
        },

        getWidgetCssModifiers: function () {
            var counterModifier,
                dropModifier;

            counterModifier = this.messengers.length <= this.cssModifiers.maxCircleMessengers
                ? '-items-' + this.messengers.length
                : '-items-' + this.cssModifiers.maxCircleMessengers;
            dropModifier = this.messengers.length > this.cssModifiers.maxCircleMessengers
                || this.widgetPosition.includes('center')
                ? this.cssModifiers.lineOpenedView
                : this.cssModifiers.circleOpenedView;

            return counterModifier + ' ' + dropModifier;
        }
    });
});
