var config = {
    map: {
        '*': {
            //Step Validator
            'stepValidator': 'Elsnertech_OneStepCheckout/js/view/step-validator',
            // Javascript Override
            'Magento_Checkout/js/view/summary/totals':
                'Elsnertech_OneStepCheckout/js/view/summary/totals',
            'Magento_Checkout/js/view/shipping':
                'Elsnertech_OneStepCheckout/js/view/shipping',
            'Magento_Checkout/js/view/form/element/email':
                'Elsnertech_OneStepCheckout/js/view/form/element/email',
            'Magento_Checkout/js/view/summary/cart-items':
                'Elsnertech_OneStepCheckout/js/view/summary/cart-items',
            'Magento_SalesRule/js/view/payment/discount':
                'Elsnertech_OneStepCheckout/js/view/payment/discount',
            'Magento_Checkout/js/view/estimation':
                'Elsnertech_OneStepCheckout/js/view/estimation',
            'Magento_Checkout/js/view/shipping-address/address-renderer/default':
                'Elsnertech_OneStepCheckout/js/view/shipping-address/address-renderer/default',
            'Magento_Tax/js/view/checkout/summary/shipping':
                'Elsnertech_OneStepCheckout/js/view/checkout/summary/shipping',
            'Magento_Tax/js/view/checkout/summary/tax':
                'Elsnertech_OneStepCheckout/js/view/checkout/summary/tax',
            'Magento_Checkout/js/model/customer-email-validator':
                'Elsnertech_OneStepCheckout/js/model/customer-email-validator',

            // Html template override
            'Magento_Checkout/template/shipping.html':
                'Elsnertech_OneStepCheckout/template/shipping.html',
            'Magento_Checkout/template/payment.html':
                'Elsnertech_OneStepCheckout/template/payment.html',
            'Magento_Checkout/template/progress-bar.html':
                'Elsnertech_OneStepCheckout/template/progress-bar.html',
            'Magento_Checkout/template/form/element/email.html':
                'Elsnertech_OneStepCheckout/template/form/element/email.html',
            'Magento_SalesRule/template/payment/discount.html':
                'Elsnertech_OneStepCheckout/template/payment/discount.html',
            'Magento_Checkout/template/summary/totals.html':
                'Elsnertech_OneStepCheckout/template/summary/totals.html',
            'Magento_Checkout/template/summary/cart-items.html':
                'Elsnertech_OneStepCheckout/template/summary/cart-items.html',
            'Magento_Checkout/template/summary.html':
                'Elsnertech_OneStepCheckout/template/summary.html',
            'Magento_Tax/template/checkout/summary/subtotal.html':
                'Elsnertech_OneStepCheckout/template/checkout/summary/subtotal.html',
            'Magento_Checkout/template/cart/totals/subtotal.html':
                'Elsnertech_OneStepCheckout/template/cart/totals/subtotal.html',
            'Magento_Tax/template/checkout/summary/shipping.html':
                'Elsnertech_OneStepCheckout/template/checkout/summary/shipping.html',
            'Magento_Checkout/template/payment-methods/list.html':
                'Elsnertech_OneStepCheckout/template/payment-methods/list.html',
            'Magento_Checkout/template/estimation.html':
                'Elsnertech_OneStepCheckout/template/estimation.html'
        }
    }
};