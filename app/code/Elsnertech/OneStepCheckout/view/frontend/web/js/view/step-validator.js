define([
    'jquery',
    'uiComponent',
    'ko',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer'
], function (
    $,
    Component,
    ko,
    registry,
    quote,
    customer,
) {
    'use strict';
    return Component.extend({
        initialize: function () {
            this._super();
            var self = this;
            self.initializeStep();
            self.initializePopupOpen();
            window.addEventListener('open-next-popup', (event) => {
                self.openRespectedStep(event.detail?.hideErrorMessage);
            });
            window.addEventListener('place-order', (event) => {
                self.validateAndPlaceOrder(event);
            });
            window.addEventListener('validate-shipping-address-from-list', (event) => {
                self.validateShippingAddressFromList(event?.detail?.hideErrorMessage || false);
            });
            window.addEventListener('validate-billing-address-from-list', (event) => {
                self.validateBillingAddressFromList(event?.detail?.hideErrorMessage || false);
            });
            window.addEventListener('open-shipping-address-popup', (event) => {
                self.openShippingAddressPopup(event?.detail?.hideErrorMessage || false);
            })
            window.addEventListener('open-billing-address-popup', (event) => {
                self.openBillingAddressPopup(event?.detail?.hideErrorMessage || false);
            })
            self.moveFieldsForMobile();
            $('.header.content').append(`
                <div class="secure-chekout-label"><svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="2.4rem" height="2.4rem" class=""><path d="M6.667 9.385H5a1 1 0 0 0-1 1V17a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-6.615a1 1 0 0 0-1-1h-1.667m-6.666 0V4.462C6.667 3.64 7.333 2 10 2c2.667 0 3.333 1.641 3.333 2.462v4.923m-6.666 0h6.666" stroke="#495050" stroke-width="1.6"></path></svg><span class="">Secure Checkout</span></div>
            `);
        },
        initializeStep: function () {
            let x = window.setInterval(() => {
                if ($('#email-step-popup').length) {
                    window.clearInterval(x);
                    window.setTimeout(() => {
                        this.openRespectedStep();
                    }, 1000);
                }
            }, 250);
        },
        validateAndPlaceOrder: function () {
            this.openRespectedStep(false, true);
        },
        placeOrder: function () {
            $('.payment-method._active .action.primary.checkout').click();
        },
        openRespectedStep: function (hideErrorMessage = null, placeOrder = false) {
            var self = this;
            var hideValidationMessage = (hideErrorMessage != null ? hideErrorMessage : true);
            registry.get('checkout.steps.shipping-step.shippingAddress.customer-email', function (component) {
                if (component.isValidStep(hideValidationMessage)) {
                    registry.get('checkout.steps.shipping-step.shippingAddress', function (component) {
                        if (component.isValidStep(hideValidationMessage)) {
                            if (component.validateBillingInformation(hideValidationMessage)) {
                                if (!quote.shippingMethod()) {
                                    self.openShippingMethodSelectorPopup();
                                } else {
                                    if (!quote.paymentMethod()) {
                                        self.openPaymentMethodSelectorPopup();
                                    } else {
                                        if (placeOrder) {
                                            self.placeOrder();
                                        } else {
                                            self.openPaymentMethodSelectorPopup();
                                        }
                                    }
                                }
                            } else {
                                self.openBillingAddressPopup(true);
                            }

                        } else {
                            self.openShippingAddressPopup(true);
                        }
                    });
                } else {
                    self.openEmailPopup(true);
                }
            });
        },
        openEmailPopup: function () {
            $("#email-step-popup").modal('openModal');
        },
        openShippingAddressPopup: function (hideErrorMessage = false) {
            var self = this;
            $('#shipping-address-popup').modal('openModal');
            window.setTimeout(() => {
                self.validateShippingAddressFromList(hideErrorMessage);
            }, 1000);
        },
        validateShippingAddressFromList: function (hideErrorMessage) {
            if ($('.shipping-address-item.selected-item')) {
                registry.get('checkout.steps.shipping-step.shippingAddress', function (component) {
                    if (!component.isValidStep(hideErrorMessage)) {
                        if ($('.shipping-address-item.selected-item button.action.edit-address-link').css('display') != 'none') {
                            $('.shipping-address-item.selected-item button.action.edit-address-link').click();
                        } else {
                            if ($('.shipping-address-item.selected-item button.action.action-select-shipping-item').length == 0 && $('.shipping-address-item.selected-item button.action.edit-address-link').css('display') != 'none') {
                                $('.shipping-address-item.selected-item button.action.edit-address-link').click();
                            }
                        }
                    }
                });
            }
        },
        openBillingAddressPopup: function (hideErrorMessage = false) {
            var self = this;
            $('#billing-address-popup').modal('openModal');
            window.setTimeout(() => {
                self.validateBillingAddressFromList(hideErrorMessage);
            }, 1000)
        },
        validateBillingAddressFromList: function (hideErrorMessage) {
            if ($('.billing-address-item.selected-item')) {
                if ($('.billing-address-item.selected-item button.action.edit-address-link').css('display') != 'none') {
                    registry.get('checkout.steps.shipping-step.shippingAddress', function (component) {
                        if (!component.validateBillingInformation(hideErrorMessage)) {
                            $('.billing-address-item.selected-item button.action.edit-address-link').click();
                        }
                    });
                } else {
                    if ($('.billing-address-item.selected-item button.action.action-select-billing-item').length == 0 && $('.billing-address-item.selected-item button.action.edit-address-link').css('display') != 'none') {
                        $('.billing-address-item.selected-item button.action.edit-address-link').click();
                    }
                }
            }
        },
        openShippingMethodSelectorPopup: function () {
            $('#shipping-method-popup').modal('openModal');
        },
        openPaymentMethodSelectorPopup: function () {
            $('#payment-method-popup').modal('openModal');
        },
        openCartItemsPopupMobile: function () {
            $('#cart-items-popup').modal('openModal');
        },
        initializePopupOpen: function () {
            var self = this;
            if (!customer.isLoggedIn()) {
                let a = window.setInterval(() => {
                    if ($(`[data-trigger="trigger-email"]`).length) {
                        $(`[data-trigger="trigger-email"]`).click(function () { self.openEmailPopup() });
                        window.clearInterval(a);
                    }
                }, 100);
            }

            let b = window.setInterval(() => {
                if ($(`[data-trigger="trigger-shipping-address"]`).length) {
                    $(`[data-trigger="trigger-shipping-address"]`).click(function () {
                        registry.get('checkout.steps.shipping-step.shippingAddress.customer-email', function (component) {
                            if (component.isValidStep(false)) {
                                self.openShippingAddressPopup();
                            } else {
                                self.openEmailPopup();
                            }
                        })
                    });
                    window.clearInterval(b);
                }
            }, 100);


            let c = window.setInterval(() => {
                if ($(`[data-trigger="trigger-billing-address"]`).length) {
                    $(`[data-trigger="trigger-billing-address"]`).click(function () {
                        registry.get('checkout.steps.shipping-step.shippingAddress.customer-email', function (component) {
                            if (component.isValidStep(false)) {
                                registry.get('checkout.steps.shipping-step.shippingAddress', function (component) {
                                    if (component.isValidStep(false)) {
                                        self.openBillingAddressPopup();
                                    } else {
                                        self.openShippingAddressPopup();
                                    }
                                });
                            } else {
                                self.openEmailPopup();
                            }
                        });
                    });
                    window.clearInterval(c);
                }
            }, 100);


            let d = window.setInterval(() => {
                if ($(`[data-trigger="trigger-shipping-method"]`).length) {
                    $(`[data-trigger="trigger-shipping-method"]`).click(function () {
                        registry.get('checkout.steps.shipping-step.shippingAddress.customer-email', function (component) {
                            if (component.isValidStep(false)) {
                                registry.get('checkout.steps.shipping-step.shippingAddress', function (component) {
                                    if (component.isValidStep(false)) {
                                        if (component.validateBillingInformation(false)) {
                                            self.openShippingMethodSelectorPopup();
                                        } else {
                                            self.openBillingAddressPopup();
                                        }

                                    } else {
                                        self.openShippingAddressPopup();
                                    }
                                });
                            } else {
                                self.openEmailPopup();
                            }
                        });
                    });
                    window.clearInterval(d);
                }
            }, 100);


            let e = window.setInterval(() => {
                if ($(`[data-trigger="trigger-payment-method"]`).length) {
                    $(`[data-trigger="trigger-payment-method"]`).click(function () {
                        registry.get('checkout.steps.shipping-step.shippingAddress.customer-email', function (component) {
                            if (component.isValidStep(false)) {
                                registry.get('checkout.steps.shipping-step.shippingAddress', function (component) {
                                    if (component.isValidStep(false)) {
                                        if (component.validateBillingInformation(false)) {
                                            if (!quote.shippingMethod()) {
                                                self.openShippingMethodSelectorPopup();
                                            } else {
                                                self.openPaymentMethodSelectorPopup();
                                            }
                                        } else {
                                            self.openBillingAddressPopup();
                                        }

                                    } else {
                                        self.openShippingAddressPopup();
                                    }
                                });
                            } else {
                                self.openEmailPopup();
                            }
                        });
                    });
                    window.clearInterval(e);
                }
            }, 100);
        },
        moveFieldsForMobile: function () {
            var self = this;
            $(window).on('resize', function () {
                self.moveFieldsForMobileFunction();
            });
            self.moveFieldsForMobileFunction();
        },
        moveFieldsForMobileFunction: function () {
            if (window.innerWidth <= 768) {
                let x = window.setInterval(() => {
                    if ($('.data.table.table-totals').length && $('.checkout-placeorder').length && $('.opc-summary-mobile').length) {
                        $('.data.table.table-totals').detach().appendTo('.opc-summary-mobile');
                        $('.checkout-placeorder').detach().appendTo('.opc-summary-mobile');
                        window.clearInterval(x);
                    }
                }, 250);
                let y = window.setInterval(() => {
                    if ($('.account-icon-mobile').length) {
                        $('.account-icon-mobile').show();
                        window.clearInterval(y);
                    }
                }, 250);
                let z = window.setInterval(() => {
                    if ($('.mobile-cart-items').length) {
                        let a = window.setInterval(() => {
                            if ($('.block.items-in-cart').length) {
                                $('.block.items-in-cart').detach().appendTo('.mobile-cart-items');
                                window.clearInterval(a);
                            }
                        }, 250);
                        window.clearInterval(z);
                    }
                }, 250);
            }
        }
    });
});
