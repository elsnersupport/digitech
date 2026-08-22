/**
 * Selections Column with radiobutton with switchable options group
 */
define([
    'knockout',
    'underscore',
    'Magento_Ui/js/grid/columns/multiselect'
], function (ko, _, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            headerTmpl: 'ui/grid/columns/text',
            bodyTmpl: 'Amasty_ShopByQuickConfig/grid/radiobutton',
            sortable: true,
            draggable: true,
            fieldClass: {
                'data-grid-checkbox-cell': false
            },

            /**
             * key - option group id. determines by optionGroupField
             * value - array of OptionItem
             *
             * @typedef {Object} OptionItem
             * @param {String|Number} value
             * @param {String} label
             */
            options: {},
            optionGroupField: 'type',

            /**
             * For each row dedicated "Observable" item variable
             */
            rowsObservers: {},
            selected: {}
        },

        /**
         * @param {Object} row
         * @returns {OptionItem[]}
         */
        resolveOption: function (row) {
            var rowGroup = row[this.optionGroupField];

            return this.fixOptionsArray(this.options[rowGroup]);
        },

        /**
         * Magento 2.3 compatibility fix.
         *
         * @param {Array|Object} options
         * @returns {Array}
         */
        fixOptionsArray: function (options) {
            if (!_.isArray(options) && _.isObject(options)) {
                delete options.__disableTmpl;

                return Object.values(options);
            }

            return options;
        },

        /**
         * Is option value same as saved.
         *
         * @param {String|Number} optionValue
         * @param {Object} row
         * @returns {Boolean}
         */
        isOptionValueOld: function (optionValue, row) {
            return optionValue == row[this.index]; // eslint-disable-line eqeqeq
        },

        /**
         * @param {Object} row
         * @returns {Function}
         */
        resolveSelectedObserver: function (row) {
            var id = row[this.indexField];

            if (_.isUndefined(this.rowsObservers[id])) {
                this.rowsObservers[id] = ko.observable(row[this.index]);
                this.rowsObservers[id].subscribe(this.selectionSubscriber.bind(this, id));
            }

            return this.rowsObservers[id];
        },

        /**
         * Register selected value to save if necessary.
         *
         * @param {Number|String} id
         * @param {Number|String} selectedValue
         * @returns {void}
         */
        selectionSubscriber: function (id, selectedValue) {
            var dataToSave = this.selected(),
                row = this.getRowById(id);

            if (!row) {
                return;
            }

            if (this.isOptionValueOld(selectedValue, row)) {
                delete dataToSave[id];
            } else {
                dataToSave[id] = selectedValue;
            }

            this.selected(dataToSave);
        },

        /**
         * @param {Number|String} id
         * @returns {Object|Undefined}
         */
        getRowById: function (id) {
            var searchCriteria = {};

            searchCriteria[this.indexField] = id;

            return _.find(this.rows(), searchCriteria);
        },

        /**
         * @inheritdoc
         */
        onRowsChange: function () {
            this._super();

            this.fixDataUpdate();

            return this;
        },

        /**
         * Force update each observable radio selector.
         * HTML may not show selected radio on data update, because of fastForeach binding
         *
         * @returns {void}
         */
        fixDataUpdate: function () {
            _.each(this.rows(), function (row) {
                var observer = this.resolveSelectedObserver(row);

                observer.valueHasMutated();
            }, this);
        }
    });
});
