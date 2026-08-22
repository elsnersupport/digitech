/**
 * Generic form statuses
 */
define([], function () {
    'use strict';

    return {
        isChanged: false,

        /**
         * @param {jQuery} $form
         * @return {void}
         */
        setInitialState: function ($form) {
            $form.one('changed', function () {
                this.isChanged = true;
            }.bind(this));
        },

        /**
         * @return {void}
         */
        resetState: function () {
            this.isChanged = false;
        },

        /**
         * @return {boolean}
         */
        isFormModified: function () {
            return this.isChanged;
        }
    };
});
