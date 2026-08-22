/**
 * Filters form provider.
 */
define([
    'ko',
    'jquery',
    'underscore',
    'Magento_Ui/js/form/provider',
    'uiRegistry',
    'mageUtils',
    'Amasty_ShopByQuickConfig/js/model/active-filter',
    'rjsResolver'
], function (ko, $, _, Provider, registry, utils, activeFilter, rjsResolver) {
    'use strict';

    return Provider.extend({
        defaults: {
            itemIdKey: 'filter_code',
            itemCompareKey: 'position',
            saveKeys: ['filter_code', 'position'],
            itemDataKeys: ['side_items', 'top_items'],
            ignoreTmpls: {
                originData: true
            }
        },

        /**
         * Initializes provider component.
         *
         * @returns {Provider} Chainable.
         */
        initialize: function () {
            this._super();

            rjsResolver(function () {
                this.set('params.isSaveAvailable', true);
            }.bind(this));

            return this;
        },

        initObservable: function () {
            this._super();

            this.on('data.reset', this.reset.bind(this));

            return this;
        },

        initConfig: function () {
            this._super();

            this.originData = this.data;

            return this;
        },

        /**
         * Compare stored and origin currently edited item data.
         *
         * @return {boolean}
         */
        isCurrentFilterEdited: function () {
            var filterCode = activeFilter.activeFilterCode(),
                result = false;

            if (!filterCode) {
                return result;
            }

            _.find(this.originData, function (items, blockName) {
                var parentPath,
                    originItem,
                    item;

                if (!_.isArray(items)) {
                    return false;
                }

                parentPath = utils.fullPath('data', blockName);
                originItem = _.find(items, { 'filter_code': filterCode });
                item = _.find(this.get(parentPath), { 'filter_code': filterCode });

                if (_.isObject(originItem)) {
                    delete originItem['record_id'];
                }

                if (_.isObject(item)) {
                    delete item['record_id'];
                }

                result = !_.isEqual(item, originItem);

                return result;
            }, this);

            return result;
        },

        reset: function () {
            this.set('params.isSaveAvailable', false);

            this.setData(this.get('data'), this.originData, this.data, 'data');

            this.set('params.isSaveAvailable', true);
        },

        /**
         * Update data that stored in provider.
         *
         * @param {Boolean} isProvider
         * @param {Object} newData
         *
         * @returns {Provider}
         */
        updateConfig: function (isProvider, newData) {
            if (isProvider === true) {
                // deep data clone
                this.originData = $.extend({}, newData.data);
            }

            return this._super();
        },

        /**
         * Saves currently available data.
         *
         * @param {Object} [options] - Addtitional request options.
         * @returns {Provider} Chainable.
         */
        save: function (options) {
            if (this.isDataChanged()) {
                this.client.save(this.getDataForSave(), options);
            }

            return this;
        },

        /**
         * Prepare data for save.
         *
         * @returns {Object}
         */
        getDataForSave: function () {
            var saveData = this.get('data');

            _.each(this.itemDataKeys, function (dataKey) {
                var items = saveData[dataKey];

                saveData[dataKey] = [];

                _.each(items, function (item) {
                    if (!item) {
                        return;
                    }

                    saveData[dataKey].push(_.pick(item, this.saveKeys));
                }, this);
            }, this);

            return saveData;
        },

        /**
         * Checks is items data differs from origin data
         *
         * @returns {Boolean}
         */
        isDataChanged: function () {
            return !!_.find(this.itemDataKeys, function (dataKey) {
                var originData = this.originData[dataKey],
                    items = this.get('data.' + dataKey);

                return !!_.find(items, function (item) {
                    var criteria = {},
                        oldItem;

                    if (!item || _.isUndefined(item[this.itemIdKey])) {
                        return false;
                    }

                    criteria[this.itemIdKey] = item[this.itemIdKey];

                    oldItem = _.findWhere(originData, criteria);

                    return !this._compareItems(item, oldItem);
                }, this);
            }, this);
        },

        /**
         * @param {Object} newItem
         * @param {Object} oldItem
         * @returns {Boolean}
         * @private
         */
        _compareItems: function (newItem, oldItem) {
            return _.isEqual(newItem[this.itemCompareKey], oldItem[this.itemCompareKey]);
        },

        /**
         *  Set data to provider based on current data.
         *  Overridden to fix behavior when items are added or deleted.
         *
         * @param {Object} oldData
         * @param {Object} newData
         * @param {Provider} current
         * @param {String} parentPath
         * @returns {void}
         */
        setData: function (oldData, newData, current, parentPath) {
            _.each(newData, function (val, key) {
                if (_.isArray(val)) {
                    this._updateDynamicRowsData(key, val, oldData[key]);
                }

                if (_.isUndefined(oldData[key])) {
                    this.set(utils.fullPath(parentPath, key), val);
                } else if (_.isObject(val) || _.isArray(val)) {
                    this.setData(oldData[key], val, current[key], utils.fullPath(parentPath, key));
                } else if (val != oldData[key]) { // eslint-disable-line eqeqeq
                    this.set(utils.fullPath(parentPath, key), val);
                }
            }, this);

            this._checkDelete(oldData, newData, current, parentPath);
        },

        /**
         * Process data update for Dynamic Rows components.
         *
         * Elements should be manually deleted or added.
         *
         * @param {String} key
         * @param {Array} itemsData
         * @param {Array} oldItemsData
         * @returns {void}
         * @private
         */
        _updateDynamicRowsData: function (key, itemsData, oldItemsData) {
            var newLength = itemsData.length,
                oldLength = oldItemsData.length,
                intersectionCount = newLength - oldLength,
                dynamicRowsComponent = registry.get('index = ' + key),
                id;

            if (intersectionCount > 0) {
                for (intersectionCount; intersectionCount > 0; intersectionCount--) {
                    id = newLength - intersectionCount;
                    dynamicRowsComponent.processingAddChild(itemsData[id], id);
                }
            } else if (intersectionCount < 0) {
                for (intersectionCount; intersectionCount < 0; intersectionCount++) {
                    id = oldLength + intersectionCount;
                    dynamicRowsComponent.deleteRecord(
                        id,
                        oldItemsData[id][dynamicRowsComponent.identificationProperty]
                    );
                }
            }

            dynamicRowsComponent.reloadElements();
        },

        _checkDelete: function (oldData, newData, current, parentPath) {
            _.each(oldData, function (val, key) {
                if (_.isUndefined(newData[key])) {
                    this.remove(utils.fullPath(parentPath, key));
                } else if (_.isObject(val) || _.isArray(val)) {
                    this._checkDelete(val, newData[key], current[key], utils.fullPath(parentPath, key));
                }
            }, this);
        }
    });
});
