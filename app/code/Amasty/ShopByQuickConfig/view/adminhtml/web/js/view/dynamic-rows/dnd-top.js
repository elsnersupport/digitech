/**
 * Drag and drop for dynamic rows.
 * Left/Right separators with multi lines.
 */
define([
    'jquery',
    'underscore',
    'Magento_Ui/js/dynamic-rows/dnd',
    'Amasty_ShopByQuickConfig/js/action/move-element'
], function ($, _, Element, moveElement) {
    'use strict';

    return Element.extend({
        defaults: {
            separatorsClass: {
                top: '_dragover-left',
                bottom: '_dragover-right'
            }
        },

        /**
         * @inheritDoc
         */
        initialize: function () {
            this._super();

            this.mousemoveHandler = _.throttle(this.mousemoveHandler, 15, { trailing: false });

            return this;
        },

        disableSelect: function (event) {
            event.preventDefault();
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

        /**
         * Mouse down handler
         *
         * @param {Object} data - element data
         * @param {HTMLDivElement|HTMLElement} elem - element
         * @param {jQuery.Event} event - key down event
         * @return {void}
         */
        mousedownHandler: function (data, elem, event) {
            var recordNode = this.getRecordNode(elem),
                $originRecord = $(elem).closest('tr').eq(0),
                drEl = this.draggableElement,
                $table = $(elem).closest('table').eq(0),
                $tableWrapper = $table.parent();

            this.disableScroll();
            $(recordNode).addClass(this.draggableElementClass);
            $originRecord.addClass(this.draggableElementClass);
            this.step = this.step === 'auto' ? $originRecord.outerWidth(true) / 2 : this.step;
            drEl.originRow = $originRecord;
            recordNode = this.processingStyles(recordNode, elem);
            drEl.instance = recordNode;
            drEl.instanceCtx = this.getRecord($originRecord[0]);
            drEl.eventMousedownY = this.getPageY(event);
            drEl.eventMousedownX = this.getPageX(event);
            drEl.minYpos =
                $table.offset().top - $originRecord.offset().top + ($table.children('thead').outerHeight() || 0);
            drEl.maxYpos = drEl.minYpos + $table.children('tbody').outerHeight() - $originRecord.outerHeight();

            drEl.minXpos =
                $table.offset().left - $originRecord.offset().left + ($table.children('thead').outerWidth() || 0);
            drEl.maxXpos = drEl.minXpos + $table.children('tbody').outerWidth() - $originRecord.outerWidth();
            $tableWrapper.append(recordNode);
            this.body.bind('mousemove touchmove', this.mousemoveHandler);
            this.body.bind('mouseup touchend', this.mouseupHandler);
            this.body.bind('selectstart', this.disableSelect);

            this.isDragging(true);
        },

        /**
         * Mouse move handler
         *
         * @param {jQuery.Event} event - mouse move event
         * @return {void}
         */
        mousemoveHandler: function (event) {
            var depEl = this.draggableElement,
                positionY = this.getPageY(event) - depEl.eventMousedownY,
                positionX = this.getPageX(event) - depEl.eventMousedownX,
                depElement = this.getDepElement(depEl.instance, positionX, positionY, depEl.originRow);

            if (depEl.depElement) {
                depEl.depElement.elem.removeClass(depEl.depElement.className);
            }

            if (depElement) {
                depEl.depElement = depElement;

                if (depEl.depElement.insert !== 'none') {
                    depEl.depElement.elem.addClass(depElement.className);
                }
            } else if (depEl.depElement && depEl.depElement.insert !== 'none') {
                depEl.depElement.insert = 'none';
            }

            moveElement(
                $(depEl.instance)[0],
                this.getAxisCoordinate(positionX, depEl.minXpos, depEl.maxXpos),
                this.getAxisCoordinate(positionY, depEl.minYpos, depEl.maxYpos)
            );
        },

        /**
         * @param {Number} currentPosition
         * @param {Number} min
         * @param {Number} max
         * @return {Number}
         */
        getAxisCoordinate: function (currentPosition, min, max) {
            if (currentPosition < min) {
                return min;
            }

            if (currentPosition >= max) {
                return max;
            }

            return currentPosition;
        },

        /**
         * Mouse up handler
         * @param {jQuery.Event} event - mouse move event
         * @return {void}
         */
        mouseupHandler: function (event) {
            var depElementCtx,
                drEl = this.draggableElement,
                positionX = this.getPageX(event) - drEl.eventMousedownX,
                positionY = this.getPageY(event) - drEl.eventMousedownY;

            this.enableScroll();
            drEl.depElement = this.getDepElement(drEl.instance, positionX, positionY, this.draggableElement.originRow);

            drEl.instance.remove();

            if (drEl.depElement) {
                depElementCtx = this.getRecord(drEl.depElement.elem[0]);
                drEl.depElement.elem.removeClass(drEl.depElement.className);

                if (drEl.depElement.insert !== 'none') {
                    this.setPosition(drEl.depElement.elem, depElementCtx, drEl);
                }
            }

            drEl.originRow.removeClass(this.draggableElementClass);

            this.body.unbind('mousemove touchmove', this.mousemoveHandler);
            this.body.unbind('mouseup touchend', this.mouseupHandler);
            this.body.unbind('selectstart', this.disableSelect);

            this.draggableElement = {};

            this.isDragging(false);
        },

        /**
         * Get dependency element
         *
         * @param {HTMLElement} curInstance - current element instance
         * @param {Number} positionX
         * @param {Number} positionY
         * @param {jQuery|HTMLElement} row
         * @return {Object}
         */
        getDepElement: function (curInstance, positionX, positionY, row) {
            var tableSelector = this.tableClass + ' tr',
                $table = $(row).closest('table').eq(0),
                $curInstance = $(curInstance),
                $curInstanceItem = $curInstance.find('tbody > tr'),
                leftPosition = $curInstance.position().left + $curInstanceItem.position().left,
                recordsCollection,
                position = 'before',
                curInstancePositionX = leftPosition,
                curInstancePositionY;

            if ($table.find('table').length) {
                recordsCollection = $table.find('tbody > tr').filter(function (index, elem) {
                    return !$(elem).parents(tableSelector).length;
                });
            } else {
                recordsCollection = $table.find('tbody > tr');
            }

            if (positionX > 0) {
                position = 'after';
                curInstancePositionX = leftPosition + $curInstanceItem.outerWidth();
            }

            curInstancePositionY = $curInstance.position().top
                + $curInstanceItem.position().top
                + ($curInstanceItem.outerHeight() / 2);

            return this._getDepElement(recordsCollection, position, curInstancePositionX, curInstancePositionY);
        },

        /**
         * Get dependency element private
         *
         * @param {jQuery} collection - record collection
         * @param {String} position - position to add
         * @param {Number} dragPositionX - position drag element
         * @param {Number} dragPositionY - position drag element
         * @return {Object}
         */
        _getDepElement: function (collection, position, dragPositionX, dragPositionY) {
            var rec,
                rangeEnd,
                rangeStart,
                lineRangeTop,
                lineRangeBottom,
                result,
                className,
                i = 0,
                length = collection.length;

            for (i; i < length; i++) {
                rec = collection.eq(i);

                lineRangeTop = rec.position().top;
                lineRangeBottom = rec.position().top + rec.outerHeight(true);

                if (position === 'before') {
                    rangeStart = rec.position().left - this.step;
                    rangeEnd = rangeStart + this.step * 2;
                    className = this.separatorsClass.top;
                } else if (position === 'after') {
                    rangeEnd = rec.position().left + rec.outerWidth(true) + this.step;
                    rangeStart = rangeEnd - this.step * 2;
                    className = this.separatorsClass.bottom;
                }

                if (dragPositionX >= rangeStart && dragPositionX <= rangeEnd
                    && dragPositionY >= lineRangeTop && dragPositionY <= lineRangeBottom
                ) {
                    result = {
                        elem: rec,
                        insert: rec[0] === this.draggableElement.originRow[0] ? 'none' : position,
                        className: className
                    };
                }
            }

            if (!result && rec) {
                result = {
                    elem: rec,
                    insert: rec[0] === this.draggableElement.originRow[0] ? 'none' : 'after',
                    className: this.separatorsClass.bottom
                };
            }

            return result;
        },

        /**
         * Get correct page X
         *
         * @param {jQuery.Event} event - current event
         * @returns {Number}
         */
        getPageX: function (event) {
            var pageX;

            if (event.type.indexOf('touch') >= 0) {
                if (event.originalEvent.touches[0]) {
                    pageX = event.originalEvent.touches[0].pageX;
                } else {
                    pageX = event.originalEvent.changedTouches[0].pageX;
                }
            } else {
                pageX = event.pageX;
            }

            return pageX;
        },

        /**
         * Overridden to prevent set width
         * @returns {void}
         */
        _setTableWidth: function () {
        }
    });
});
