/**
 * Moves specified DOM element to the x and y coordinates.
 */
define([], function () {
    'use strict';

    var transformProp;

    /**
     * Defines supported css 'transform' property.
     *
     * @returns {String}
     */
    transformProp = (function () { // eslint-disable-line consistent-return
        var style = document.documentElement.style,
            base = 'Transform',
            vendors = ['webkit', 'moz', 'ms', 'o'],
            vi = vendors.length,
            property;

        if (typeof style.transform != 'undefined') {
            return 'transform';
        }

        while (vi--) {
            property = vendors[vi] + base;

            if (typeof style[property] != 'undefined') {
                return property;
            }
        }
    }());

    /**
     * Moves specified DOM element to the x and y coordinates.
     *
     * @param {HTMLElement} elem - Element to be relocated.
     * @param {Number} x - X coordinate.
     * @param {Number} y - Y coordinate.
     * @returns {void}
     */
    return function (elem, x, y) {
        elem.style[transformProp] = 'translate(' + x + 'px,' + y + 'px)';
    };
});
