/*
 * *
 *  * Copyright © 2025 MoneyHash. All rights reserved.
 *  *
 *  * Developed by: Ahmed Allam
 *
 *  *
 *  * Project: MoneyHash Payment Integration Extension for Magento 2
 *  *
 *  * NOTICE OF LICENSE
 *  * This source file is subject to the proprietary license that is bundled
 *  * with this package in the file LICENSE.txt. It is also available through
 *  * the world-wide-web at:
 *  * https://moneyhash.io/
 *
 *
 */
define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'mage/url',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer'
    ],
    function (quote, urlBuilder, storage, url, errorProcessor, customer) {
        'use strict';

        return function (paymentData, messageContainer) {
            var serviceUrl,
                payload;

            if (customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/moneyhash/intent/create', {});
                payload = {
                    cartId: quote.getQuoteId(),
                    paymentMethod: paymentData,
                    billingAddress: quote.billingAddress()
                };
            } else {
                serviceUrl = urlBuilder.createUrl('/moneyhash/intent/:quoteId/create', {
                    quoteId: quote.getQuoteId()
                });
                payload = {
                    cartId: quote.getQuoteId(),
                    email: quote.guestEmail,
                    paymentMethod: paymentData,
                    billingAddress: quote.billingAddress()
                };
            }

            return storage.post(
                serviceUrl, JSON.stringify(payload)
            ).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                }
            );
        };
    }
);
