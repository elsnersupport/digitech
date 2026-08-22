/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/form',
    'ko',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry',
    'mage/translate',
    'Magento_Checkout/js/model/shipping-rate-service',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/action/create-billing-address',
    'Magento_Checkout/js/model/cart/totals-processor/default',
    'Magento_Catalog/js/price-utils',
], function (
    $,
    _,
    Component,
    ko,
    customer,
    addressList,
    addressConverter,
    quote,
    createShippingAddress,
    selectShippingAddress,
    shippingRatesValidator,
    formPopUpState,
    shippingService,
    selectShippingMethodAction,
    rateRegistry,
    setShippingInformationAction,
    stepNavigator,
    modal,
    checkoutDataResolver,
    checkoutData,
    registry,
    $t,
    shippingRateService,
    selectBillingAddress,
    createBillingAddress,
    defaultTotals,
    priceUtils
) {
    'use strict';

    var popUp = null;

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping',
            shippingFormTemplate: 'Magento_Checkout/shipping-address/form',
            shippingMethodListTemplate: 'Magento_Checkout/shipping-address/shipping-method-list',
            shippingMethodItemTemplate: 'Magento_Checkout/shipping-address/shipping-method-item',
            imports: {
                countryOptions: '${ $.parentName }.shippingAddress.shipping-address-fieldset.country_id:indexedOptions'
            }
        },
        visible: ko.observable(!quote.isVirtual()),
        errorValidationMessage: ko.observable(false),
        isCustomerLoggedIn: customer.isLoggedIn,
        isFormPopUpVisible: formPopUpState.isVisible,
        isFormInline: addressList().length === 0,
        isNewAddressAdded: ko.observable(false),
        saveInAddressBook: 1,
        quoteIsVirtual: quote.isVirtual(),
        isShowBillingForm: ko.observable(false),
        isAddressSameAsShipping: ko.observable(true),
        /**
         * @return {exports}
         */
        initialize: function () {
            var self = this,
                hasNewAddress,
                fieldsetName = 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset';

            this._super();
            window.addEventListener('update-address-step-label', (event) => {
                self.updateAddressStepLabel(event.detail.address, event.detail.selector);
            });
            if (!quote.isVirtual()) {
                stepNavigator.registerStep(
                    'shipping',
                    '',
                    $t('Shipping'),
                    this.visible, _.bind(this.navigate, this),
                    this.sortOrder
                );
            }
            checkoutDataResolver.resolveShippingAddress();

            hasNewAddress = addressList.some(function (address) {
                return address.getType() == 'new-customer-address'; //eslint-disable-line eqeqeq
            });

            this.isNewAddressAdded(hasNewAddress);

            this.isFormPopUpVisible.subscribe(function (value) {
                if (value) {
                    self.getPopUp().openModal();
                }
            });

            quote.shippingMethod.subscribe(function (method) {
                self.errorValidationMessage(false);
                self.updateShippingMethodStepLabel(method);
            });
            quote.paymentMethod.subscribe(function (method) {
                self.updatePaymentMethodStepLabel(method);
                self.errorValidationMessage(false);
            });
            if (quote.shippingAddress() && quote.shippingAddress().customerAddressId && quote.shippingAddress().customerAddressId.length) {
                self.updateAddressStepLabel(quote.shippingAddress(), '#shippingAddressStepLabel');
            }
            if (quote.billingAddress() && quote.billingAddress().customerAddressId && quote.billingAddress().customerAddressId.length) {
                self.updateAddressStepLabel(quote.billingAddress(), '#billingAddressStepLabel');
            }
            registry.async('checkoutProvider')(function (checkoutProvider) {
                var shippingAddressData = checkoutData.getShippingAddressFromData();
                var billingAddressData = checkoutData.getBillingAddressFromData();
                if (shippingAddressData) {
                    checkoutProvider.set(
                        'shippingAddress',
                        $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                    );
                    self.updateAddressStepLabel(shippingAddressData, '#shippingAddressStepLabel');
                }
                if (billingAddressData) {
                    self.updateAddressStepLabel(billingAddressData, '#billingAddressStepLabel');
                }
                checkoutProvider.on('billingAddress', function (billingAddrsData, changes) {
                    self.updateAddressStepLabel(billingAddrsData, '#billingAddressStepLabel');
                })
                checkoutProvider.on('shippingAddress', function (shippingAddrsData, changes) {
                    var isStreetAddressDeleted, isStreetAddressNotEmpty;
                    /**
                     * In last modifying operation street address was deleted.
                     * @return {Boolean}
                     */
                    isStreetAddressDeleted = function () {
                        var change;

                        if (!changes || changes.length === 0) {
                            return false;
                        }

                        change = changes.pop();

                        if (_.isUndefined(change.value) || _.isUndefined(change.oldValue)) {
                            return false;
                        }

                        if (!change.path.startsWith('shippingAddress.street')) {
                            return false;
                        }

                        return change.value.length === 0 && change.oldValue.length > 0;
                    };

                    isStreetAddressNotEmpty = shippingAddrsData.street && !_.isEmpty(shippingAddrsData.street[0]);

                    if (isStreetAddressNotEmpty || isStreetAddressDeleted()) {
                        checkoutData.setShippingAddressFromData(shippingAddrsData);
                        self.updateAddressStepLabel(shippingAddrsData, '#shippingAddressStepLabel');
                    }
                });
                shippingRatesValidator.initFields(fieldsetName);
            });

            return this;
        },
        updateShippingMethodStepLabel: function (method) {
            let html = `<div class="selected-shipping-method">`;
            html += `<span class="method-title"><b>${method.method_title} ${priceUtils.formatPriceLocale(method.amount, quote.getPriceFormat())}</b></span><br>`;
            html += `<span class="carrier-title">${method.carrier_title}</span>`;
            html += `</div>`;
            $('#shippingMethodSelectStepLabel').html(html);
            $('#shippingMethodSelectStepLabel').parent().find('.change-data').show();
            $('#shippingMethodSelectStepLabel').parent().find('.initial-icon').hide();
            $('#shippingMethodSelectStepLabel').siblings('.mobile-label-after-change').show();
        },
        updatePaymentMethodStepLabel: function (method) {
            method = quote.paymentMethod();
            let y = window.setInterval(() => {
                if ($('.payment-method._active .payment-method-title label.label span').length) {
                    window.clearInterval(y);
                    let label = $('.payment-method._active .payment-method-title label.label span').text();
                    let html = `<div class="selected-billing-method">`;
                    html += `<span class="method-title"><b>${label}</b></span><br>`;
                    html += `</div>`;
                    $('#billingMethodSelectStepLabel').html(html);
                    $('#billingMethodSelectStepLabel').siblings('.mobile-label-after-change').show();
                }
            }, 100);

            let x = window.setInterval(() => {
                if (method.method == "tabby_installments") {
                    if ($('#installmentsCard').length) {
                        window.clearInterval(x);
                        window.setTimeout(() => {
                            $(document.querySelector('#installmentsCard span').shadowRoot.querySelector('div')).clone().appendTo('#billingMethodSelectStepLabel');
                        }, 1500);
                        $('.payment-method._active [data-role="checkout-messages"]').clone().appendTo('#billingMethodSelectStepLabel');
                    }
                }
                if (method.method == "ccavenue") {
                    if ($('.payment-method._active').find('.payment-method-content ul').length) {
                        window.clearInterval(x);
                        $('.payment-method._active').find('.payment-method-content ul').clone().appendTo('#billingMethodSelectStepLabel');
                        $('.payment-method._active [data-role="checkout-messages"]').clone().appendTo('#billingMethodSelectStepLabel');
                    }
                }
            }, 250);
            $('#billingMethodSelectStepLabel').parent().find('.change-data').show();
            $('#billingMethodSelectStepLabel').parent().find('.initial-icon').hide();
        },
        updateAddressStepLabel: function (address, selector) {
            if (address && Object.keys(address).length) {
                let html = `<div class="change-address">`;
                html += `<div class="current-address">`;
                if (address.firstname || address.lastname) {
                    html = `<b>${address.firstname} ${address.lastname}, </b>`;
                }
                if (address.company) {
                    html += `<span>${address.company}</span>, `;
                }
                if (address.street) {
                    Object.keys(address.street).forEach(key => {
                        html += `<span>${address.street[key]}</span>, `;
                    });
                }
                if (address.city) {
                    html += `<span>${address.city}</span>, `;
                }
                if (address.region) {
                    html += `<span>${address.region}</span>, `;
                }
                if (address.postcode) {
                    html += `<span>${address.postcode}</span>, `;
                }
                if (this.countryOptions && this.countryOptions[address.country_id]?.label) {
                    html += `<span>${this.countryOptions[address.country_id].label}</span>, `;
                }
                if (address.telephone) {
                    html += `<span>Tel : ${address.telephone}</span>`;
                }
                html += `</div></div>`;
                if (window[selector]) {
                    $(selector).html(html);
                    $(selector).parent().find('.change-data').show();
                    $(selector).parent().find('.initial-icon').hide();
                    $(selector).siblings('.mobile-label-after-change').show();
                    window[selector] = true;
                } else {
                    window.setTimeout(() => {
                        $(selector).html(html);
                        $(selector).parent().find('.change-data').show();
                        $(selector).parent().find('.initial-icon').hide();
                        $(selector).siblings('.mobile-label-after-change').show();
                    }, 1500);
                }
            }
        },
        /**
         * Navigator change hash handler.
         *
         * @param {Object} step - navigation step
         */
        navigate: function (step) {
            step && step.isVisible(true);
        },

        /**
         * @return {*}
         */
        getPopUp: function () {
            var self = this,
                buttons;

            if (!popUp) {
                buttons = this.popUpForm.options.buttons;
                this.popUpForm.options.buttons = [
                    {
                        text: buttons.save.text ? buttons.save.text : $t('Save Address'),
                        class: buttons.save.class ? buttons.save.class : 'action primary action-save-address',
                        click: self.saveNewAddress.bind(self)
                    },
                    {
                        text: buttons.cancel.text ? buttons.cancel.text : $t('Cancel'),
                        class: buttons.cancel.class ? buttons.cancel.class : 'action secondary action-hide-popup',

                        /** @inheritdoc */
                        click: this.onClosePopUp.bind(this)
                    }
                ];

                /** @inheritdoc */
                this.popUpForm.options.closed = function () {
                    self.isFormPopUpVisible(false);
                };

                this.popUpForm.options.modalCloseBtnHandler = this.onClosePopUp.bind(this);
                this.popUpForm.options.keyEventHandlers = {
                    escapeKey: this.onClosePopUp.bind(this)
                };

                /** @inheritdoc */
                this.popUpForm.options.opened = function () {
                    // Store temporary address for revert action in case when user click cancel action
                    self.temporaryAddress = $.extend(true, {}, checkoutData.getShippingAddressFromData());
                };
                popUp = modal(this.popUpForm.options, $(this.popUpForm.element));
            }

            return popUp;
        },

        /**
         * Revert address and close modal.
         */
        onClosePopUp: function () {
            checkoutData.setShippingAddressFromData($.extend(true, {}, this.temporaryAddress));
            this.getPopUp().closeModal();
        },

        /**
         * Show address form popup
         */
        showFormPopUp: function () {
            this.isFormPopUpVisible(true);
        },

        /**
         * Save new shipping address
         */
        saveNewAddress: function () {
            var addressData,
                newShippingAddress;

            this.source.set('params.invalid', false);
            this.triggerShippingDataValidateEvent();

            if (!this.source.get('params.invalid')) {
                addressData = this.source.get('shippingAddress');
                // if user clicked the checkbox, its value is true or false. Need to convert.
                addressData['save_in_address_book'] = this.saveInAddressBook ? 1 : 0;

                // New address must be selected as a shipping address
                newShippingAddress = createShippingAddress(addressData);
                selectShippingAddress(newShippingAddress);
                checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                checkoutData.setNewCustomerShippingAddress($.extend(true, {}, addressData));
                this.getPopUp().closeModal();
                this.isNewAddressAdded(true);
            }
        },

        /**
         * Shipping Method View
         */
        rates: shippingService.getShippingRates(),
        isLoading: shippingService.isLoading,
        isSelected: ko.computed(function () {
            return checkoutData.getSelectedShippingRate() ? checkoutData.getSelectedShippingRate() :
                quote.shippingMethod() ?
                    quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'] :
                    null;
        }),

        /**
         * @param {Object} shippingMethod
         * @return {Boolean}
         */
        selectShippingMethod: function (shippingMethod) {
            selectShippingMethodAction(shippingMethod);
            checkoutData.setSelectedShippingRate(shippingMethod['carrier_code'] + '_' + shippingMethod['method_code']);

            return true;
        },

        /**
         * Set shipping information handler
         */
        setShippingInformation: function (openNextStep = false) {
            var self = this;
            if (this.validateShippingInformation() && this.validateBillingInformation()) {
                quote.billingAddress(null);
                checkoutDataResolver.resolveBillingAddress();
                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var shippingAddressData = checkoutData.getShippingAddressFromData();
                    var billingAddressData = checkoutData.getBillingAddressFromData();
                    self.updateAddressStepLabel(shippingAddressData, '#shippingAddressStepLabel');
                    self.updateAddressStepLabel(billingAddressData, '#billingAddressStepLabel');
                    if (shippingAddressData) {
                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                        );
                    }
                });
                setShippingInformationAction().done(
                    function () {
                        stepNavigator.next();
                        $('#shipping-method-popup').modal('closeModal');
                        if (openNextStep) {
                            window.dispatchEvent(new CustomEvent('open-next-popup', { detail: { hideErrorMessage: true } }));
                        }
                    }
                );
            }
        },

        /**
         * @return {Boolean}
         */
        validateShippingInformation: function () {
            var shippingAddress,
                addressData,
                loginFormSelector = 'form[data-role=email-with-possible-login]',
                emailValidationResult = customer.isLoggedIn(),
                field,
                option = _.isObject(this.countryOptions) && this.countryOptions[quote.shippingAddress().countryId],
                messageContainer = registry.get('checkout.errors').messageContainer;

            if (!quote.shippingMethod()) {
                this.errorValidationMessage(
                    $t('The shipping method is missing. Select the shipping method and try again.')
                );

                return false;
            }

            if (!customer.isLoggedIn()) {
                $(loginFormSelector).validation();
                emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
            }

            if (this.isFormInline) {
                this.source.set('params.invalid', false);
                this.triggerShippingDataValidateEvent();

                if (!quote.shippingMethod()['method_code']) {
                    this.errorValidationMessage(
                        $t('The shipping method is missing. Select the shipping method and try again.')
                    );
                }

                if (emailValidationResult &&
                    this.source.get('params.invalid') ||
                    !quote.shippingMethod()['method_code'] ||
                    !quote.shippingMethod()['carrier_code']
                ) {
                    this.focusInvalid();

                    return false;
                }

                shippingAddress = quote.shippingAddress();
                addressData = addressConverter.formAddressDataToQuoteAddress(
                    this.source.get('shippingAddress')
                );

                //Copy form data to quote shipping address object
                for (field in addressData) {
                    if (addressData.hasOwnProperty(field) &&  //eslint-disable-line max-depth
                        shippingAddress.hasOwnProperty(field) &&
                        typeof addressData[field] != 'function' &&
                        _.isEqual(shippingAddress[field], addressData[field])
                    ) {
                        shippingAddress[field] = addressData[field];
                    } else if (typeof addressData[field] != 'function' &&
                        !_.isEqual(shippingAddress[field], addressData[field])) {
                        shippingAddress = addressData;
                        break;
                    }
                }

                if (customer.isLoggedIn()) {
                    shippingAddress['save_in_address_book'] = 1;
                }
                selectShippingAddress(shippingAddress);
            } else if (customer.isLoggedIn() &&
                option &&
                option['is_region_required'] &&
                !quote.shippingAddress().region
            ) {
                messageContainer.addErrorMessage({
                    message: $t('Please specify a regionId in shipping address.')
                });

                return false;
            }

            if (!emailValidationResult) {
                $(loginFormSelector + ' input[name=username]').trigger('focus');

                return false;
            }

            return true;
        },

        /**
         * Trigger Shipping data Validate Event.
         */
        triggerShippingDataValidateEvent: function () {
            this.source.trigger('shippingAddress.data.validate');

            if (this.source.get('shippingAddress.custom_attributes')) {
                this.source.trigger('shippingAddress.custom_attributes.data.validate');
            }
        },
        useShippingAddress: function () {
            if (this.isAddressSameAsShipping()) {
                this.isShowBillingForm(false);
            } else {
                this.isShowBillingForm(true);
                $(`[data-trigger="trigger-billing-address"]`).click();
            }
            return true;
        },
        validateBillingInformation: function (hideErrorMessage = false, openAddressForm = false) {
            var addressData, newBillingAddress;

            if ($('[name="billing-address-same-as-shipping"]').is(":checked")) {
                if (this.isFormInline) {
                    var shippingAddress = quote.shippingAddress();
                    addressData = addressConverter.formAddressDataToQuoteAddress(
                        this.source.get('shippingAddress')
                    );
                    //Copy form data to quote shipping address object
                    for (var field in addressData) {
                        if (addressData.hasOwnProperty(field) &&
                            shippingAddress.hasOwnProperty(field) &&
                            typeof addressData[field] !== 'function' &&
                            _.isEqual(shippingAddress[field], addressData[field])
                        ) {
                            shippingAddress[field] = addressData[field];
                        } else if (typeof addressData[field] !== 'function' &&
                            !_.isEqual(shippingAddress[field], addressData[field])) {
                            shippingAddress = addressData;
                            break;
                        }
                    }

                    if (customer.isLoggedIn()) {
                        shippingAddress.save_in_address_book = 1;
                    }
                    newBillingAddress = createBillingAddress(shippingAddress);
                    selectBillingAddress(newBillingAddress);
                } else {
                    var billingAddress = quote.shippingAddress();
                    selectBillingAddress(billingAddress);
                }

                return true;
            }

            var selectedAddress = quote.billingAddress();
            if (selectedAddress) {
                if (selectedAddress.customerAddressId) {
                    return addressList.some(function (address) {
                        if (selectedAddress.customerAddressId === address.customerAddressId) {
                            selectBillingAddress(address);
                            return true;
                        }
                        return false;
                    });
                } else if (selectedAddress.getType() === 'new-customer-address' || selectedAddress.getType() === 'new-billing-address') {
                    if (!($('.billing-address-item.selected-item button.action.action-select-billing-item').length == 0 && $('.billing-address-item.selected-item button.action.edit-address-link').css('display') != 'none')) {
                        return true;
                    }
                }
            }

            this.source.set('params.invalid', false);
            this.source.trigger('billingAddress.data.validate');

            if (this.source.get('billingAddress.custom_attributes')) {
                this.source.trigger('billingAddress.custom_attributes.data.validate');
            }
            $('.billing-address-form').find('.field-error').show();
            if (this.source.get('params.invalid')) {
                if (hideErrorMessage) {
                    $('.billing-address-form').find('.field-error').hide();
                    $('.billing-address-form').find('.field._error').removeClass('_error');
                }
                if (openAddressForm) {
                    window.dispatchEvent(new CustomEvent('validate-billing-address-from-list', { detail: { hideErrorMessage: hideErrorMessage } }));
                }
                return false;
            }

            addressData = this.source.get('billingAddress');

            if ($('#billing-save-in-address-book').is(":checked")) {
                addressData.save_in_address_book = 1;
            }
            newBillingAddress = createBillingAddress(addressData);

            selectBillingAddress(newBillingAddress);

            return true;
        },
        isValidStep: function (hideErrorMessage = false, openAddressForm = false) {
            this.source.set('params.invalid', false);
            this.triggerShippingDataValidateEvent();
            $('#co-shipping-form').find('.field-error').show();
            if (!this.source.get('params.invalid')) {
                return true;
            }
            if (hideErrorMessage) {
                $('#co-shipping-form').find('.field-error').hide();
                $('#co-shipping-form').find('.field._error').removeClass('_error');
            }
            this.focusInvalid();
            if (openAddressForm) {
                window.dispatchEvent(new CustomEvent('validate-shipping-address-from-list', { detail: { hideErrorMessage: hideErrorMessage } }));
            }
            if (!($('.shipping-address-item.selected-item button.action.action-select-shipping-item').length == 0 && $('.shipping-address-item.selected-item button.action.edit-address-link').css('display') != 'none')) {
                return true;
            }
            return false;
        },
        shippingAddressFormClose: function () {
            if (this.isValidStep(false, true)) {
                $("#shipping-address-popup").modal('closeModal');
                window.dispatchEvent(new CustomEvent('open-next-popup', { detail: { hideErrorMessage: true } }));
            }
        },
        billingAddressFormClose: function () {
            if (this.validateBillingInformation()) {
                $("#billing-address-popup").modal('closeModal');
                window.dispatchEvent(new CustomEvent('open-next-popup', { detail: { hideErrorMessage: true } }));
            }
        },
        openShippingAddressForm: function () {
            $('#shipping-method-popup').modal('closeModal');
            window.setTimeout(() => {
                window.dispatchEvent(new CustomEvent('open-shipping-address-popup'));
            }, 500);
        }
    });
});
