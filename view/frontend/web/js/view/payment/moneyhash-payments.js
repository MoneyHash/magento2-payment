/*
 * *
 *  * Copyright © 2025 MoneyHash. All rights reserved.
 *  *
 *  * Developed by: Ahmed Allam
 *  * Contact: mageserv.ltd@gmail.com (+20 102 0763062)
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

/* @api */
define([
    "uiComponent",
    "Magento_Checkout/js/model/payment/renderer-list",
], function (Component, rendererList) {
    "use strict";

    rendererList.push({
        type: "moneyhash_all",
        component:
            "MoneyHash_Payment/js/view/payment/method-renderer/moneyhash-payment-method",
    });

    /** Add view logic here if needed */
    return Component.extend({});
});
