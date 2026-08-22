define([
	'jquery',
	'ko',
	'uiComponent',
	'Magento_Checkout/js/action/select-billing-address',
	'Magento_Checkout/js/model/quote',
	'Magelearn_ImprovedCheckout/js/model/billing-address/form-popup-state',
	'Magento_Checkout/js/checkout-data',
	'Magento_Customer/js/customer-data'
], function ($, ko, Component, selectBillingAddressAction, quote, formPopUpState, checkoutData, customerData) {
	'use strict';

	var countryData = customerData.get('directory-data');

	return Component.extend({
		defaults: {
			template: 'Magelearn_ImprovedCheckout/billing-address/address-renderer/default'
		},

		/** @inheritdoc */
		initObservable: function () {
			this._super();
			this.isSelected = ko.computed(function () {
				var isSelected = false,
					billingAddress = quote.billingAddress();

				if (billingAddress) {
					isSelected = billingAddress.getKey() == this.address().getKey(); //eslint-disable-line eqeqeq
				}

				return isSelected;
			}, this);

			return this;
		},

		/**
		 * @param {String} countryId
		 * @return {String}
		 */
		getCountryName: function (countryId) {
			return countryData()[countryId] != undefined ? countryData()[countryId].name : ''; //eslint-disable-line
		},

		getCustomAttributeLabel: function (attribute) {
			var label;

			if (typeof attribute === 'string') {
				return attribute;
			}

			if (attribute.label) {
				return attribute.label;
			}

			if (_.isArray(attribute.value)) {
				label = _.map(attribute.value, function (value) {
					return this.getCustomAttributeOptionLabel(attribute['attribute_code'], value) || value;
				}, this).join(', ');
			} else if (typeof attribute.value === 'object') {
				label = _.map(Object.values(attribute.value)).join(', ');
			} else {
				label = this.getCustomAttributeOptionLabel(attribute['attribute_code'], attribute.value);
			}

			return label || attribute.value;
		},

		getCustomAttributeOptionLabel: function (attributeCode, value) {
			var option,
				label,
				options = this.source.get('customAttributes') || {};

			if (options[attributeCode]) {
				option = _.findWhere(options[attributeCode], {
					value: value
				});

				if (option) {
					label = option.label;
				}
			} else if (value.file !== null) {
				label = value.file;
			}

			return label;
		},

		/** Set selected customer shipping address  */
		selectAddress: function () {
			selectBillingAddressAction(this.address());
			checkoutData.setSelectedBillingAddress(this.address().getKey());
			window.setTimeout(() => {
				window.dispatchEvent(new CustomEvent('validate-billing-address-from-list'));
			}, 1000);
			window.dispatchEvent(new CustomEvent('update-address-step-label', { detail: { address: this.address(), selector: '#billingAddressStepLabel' } }));
		},

		/**
		 * Edit address.
		 */
		editAddress: function () {
			formPopUpState.isVisible(true);
			this.showPopup();

		},

		/**
		 * Show popup.
		 */
		showPopup: function () {
			$('[data-open-modal="opc-new-billing-address"]').trigger('click');
		}
	});
});