define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/select',
    'uiRegistry'
], function (
    $,
    _,
    Select,
    uiRegistry
) {
    'use strict';

    return Select.extend({
        defaults: {
            iconElement: '',
            customNameElement: '',
            defaultIconsField: '',
            listens: {
                'value': 'onChangeMessengerSelect'
            },
            modules: {
                // eslint-disable-next-line no-template-curly-in-string
                formProvider: '${ $.provider }'
            }
        },

        /**
         * Messenger Select Handler
         * @param {String} value
         * @returns {void}
         */
        onChangeMessengerSelect: function (value) {
            this.changeVisibilityElements(value === 'other');
        },

        /**
         * Toggle elements
         * @param {Boolean} visibility
         * @returns {void}
         */
        changeVisibilityElements: function (visibility) {
            uiRegistry.get(this.iconElement, function (element) {
                element.visible(visibility);
                uiRegistry.get(this.customNameElement).visible(visibility);
            }.bind(this));

            this.changeDefaultIcons(!visibility);
        },

        /**
         * Change icons by selected messenger
         * @param {Boolean} visibility
         * @returns {void}
         */
        changeDefaultIcons: function (visibility) {
            uiRegistry.get(this.defaultIconsField, function (element) {
                if (this.value() === '' || !this.value()) {
                    element.visible(false);
                } else {
                    element.visible(visibility);
                    element.value(element.imageMap[this.value()]);
                    element.altText(this.value());
                }
            }.bind(this));
        }
    });
});
