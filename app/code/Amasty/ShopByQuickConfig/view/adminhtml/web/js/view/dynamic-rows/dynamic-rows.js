/**
 * Optimized dynamic rows
 */
define([
    'ko',
    'underscore',
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function (ko, _, Model) {
    'use strict';

    return Model.extend({
        /**
         * Check max elements position and set if max.
         * Overridden for optimization.
         *
         * @param {Number} position - current position
         * @returns {void}
         */
        checkMaxPosition: function (position) {
            if (!this.maxPosition) {
                this.removeMaxPosition();
            }

            if (position > this.maxPosition) {
                this.maxPosition = position;
            }
        },

        /**
         * Return records with assigned position.
         *
         * @returns {Array}
         */
        getElementsWithPosition: function () {
            return this.elems().filter(function (el) {
                return el.position || el.position === 0;
            });
        },

        /**
         * Sort element by position.
         * Overridden for fix circular sort.
         *
         * @param {Number} position - element position
         * @param {Object} elem - instance
         * @returns {void}
         */
        sort: function (position, elem) {
            var sorted,
                updatedCollection;

            if (this.getElementsWithPosition().length !== this.getChildItems().length) {
                return;
            }

            if (!elem.containers.length) {
                return;
            }

            sorted = _.sortBy(this.elems(), 'position');

            updatedCollection = this.updatePosition(sorted, position, elem.name);
            this.elems(updatedCollection);
        },

        /**
         * Recollect dynamic-rows elems.
         * Trigger subscribers which rerender HTML.
         *
         * @returns {void}
         */
        reloadElements: function () {
            this.elems([]);
            this._updateCollection();
            this.removeMaxPosition();
        }
    });
});
