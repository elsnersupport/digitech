/**
 * Image Placeholder component.
 */
define([
    'underscore',
    'uiElement'
], function (_, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            visible: true
        },

        initObservable: function () {
            return this._super()
                .observe([ 'visible' ]);
        }
    });
});
