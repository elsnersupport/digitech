/**
 * Drag and drop for dynamic rows.
 * Fixes correct height calculation for items with margin.
 * And for items with different height.
 */
define([
    'jquery',
    'underscore',
    'Magento_Ui/js/dynamic-rows/dnd'
], function ($, _, Element) {
    'use strict';

    return Element.extend({
        /**
         * @inheritDoc
         */
        initialize: function () {
            this._super();

            this.mousemoveHandler = _.throttle(this.mousemoveHandler, 15, { trailing: false });

            return this;
        },

        /**
         * @inheritDoc
         */
        initObservable: function () {
            this._super()
                .observe({
                    isDragging: false
                });

            return this;
        },

        disableSelect: function (event) {
            event.preventDefault();
        },

        /**
         * Mouse down handler.
         * Overridden for fix height step and items with margin.
         *
         * @param {Object} data - element data
         * @param {Object} elem - element
         * @param {Object} event - key down event
         * @return {void}
         */
        mousedownHandler: function (data, elem, event) {
            var recordNode = this.getRecordNode(elem),
                originRecord = $(elem).parents('tr').eq(0),
                drEl = this.draggableElement,
                $table = $(elem).parents('table').eq(0),
                $tableWrapper = $table.parent();

            this.step = originRecord.outerHeight(true) / 2;

            this.isDragging(true);

            this.disableScroll();
            $(recordNode).addClass(this.draggableElementClass);
            $(originRecord).addClass(this.draggableElementClass);
            drEl.originRow = originRecord;
            drEl.instance = recordNode = this.processingStyles(recordNode, elem);
            drEl.instanceCtx = this.getRecord(originRecord[0]);
            drEl.eventMousedownY = this.getPageY(event);
            drEl.minYpos =
                $table.offset().top - originRecord.offset().top + ($table.children('thead').outerHeight() || 0);
            drEl.maxYpos = drEl.minYpos + $table.children('tbody').outerHeight() - originRecord.outerHeight();
            $tableWrapper.append(recordNode);
            this.body.on('mousemove touchmove', this.mousemoveHandler);
            this.body.on('mouseup touchend', this.mouseupHandler);
            this.body.bind('selectstart', this.disableSelect);
        },

        /**
         * Mouse up handler
         * @returns {void}
         */
        mouseupHandler: function () {
            this.isDragging(false);

            this._super();

            this.body.unbind('selectstart', this.disableSelect);
        },

        /**
         * Get dependency element.
         * Overridden for fix items with margin.
         *
         * @param {HTMLElement} curInstance - current element instance
         * @param {Number} position
         * @param {Object} row
         * @return {Object|null}
         */
        getDepElement: function (curInstance, position, row) {
            var tableSelector = this.tableClass + ' tr',
                $table = $(row).closest('table').eq(0),
                $curInstance = $(curInstance),
                recordsCollection,
                $curInstanceItem = $curInstance.find('tbody > tr'),
                curInstancePositionTop = $curInstance.position().top + $curInstanceItem.position().top;

            if ($table.find('table').length) {
                recordsCollection = $table.find('tbody > tr').filter(function (index, elem) {
                    return !$(elem).parents(tableSelector).length;
                });
            } else {
                recordsCollection = $table.find('tbody > tr');
            }

            if (position <= 0) {
                return this._getDepElement(recordsCollection, 'before', curInstancePositionTop);
            }

            return this._getDepElement(
                recordsCollection,
                'after',
                curInstancePositionTop + $curInstance.outerHeight(true)
            );
        },

        /**
         * Get dependency element private.
         * Overridden for fix dynamical height step and items with margin.
         *
         * @param {Array} collection - record collection
         * @param {String} position - position to add
         * @param {Number} dragPosition - position drag element
         * @return {Object|null}
         */
        _getDepElement: function (collection, position, dragPosition) {
            var rec,
                rangeEnd,
                rangeStart,
                result = null,
                itemHeight,
                className,
                i = 0,
                length = collection.length;

            for (i; i < length; i++) {
                rec = collection.eq(i);
                itemHeight = rec.outerHeight(true);

                if (position === 'before') {
                    rangeStart = rec.position().top - this.step;
                    rangeEnd = rangeStart + itemHeight;
                    className = this.separatorsClass.top;
                } else if (position === 'after') {
                    rangeEnd = rec.position().top + itemHeight + this.step;
                    rangeStart = rangeEnd - itemHeight;
                    className = this.separatorsClass.bottom;
                }

                if (dragPosition >= rangeStart && dragPosition <= rangeEnd) {
                    result = {
                        elem: rec,
                        insert: rec[0] === this.draggableElement.originRow[0] ? 'none' : position,
                        className: className
                    };
                }
            }

            return result;
        }
    });
});
