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
 */

define([
    "jquery",
    "Magento_Checkout/js/view/payment/default",
    "Magento_Checkout/js/model/full-screen-loader",
    "Magento_Checkout/js/model/quote",
    "ko",
    "mage/storage",
    "mage/url",
    "MoneyHash_Payment/js/action/create-intent",
    'MoneyHash_Payment/js/view/payment/moneyhash-additional-fields',
    'Magento_Ui/js/model/messageList',
    'mage/translate'
], function ($, Component, fullScreenLoader, quote, ko, storage, urlBuilder, createIntentAction, AdditionalFields, messageList) {
    "use strict";

    return Component.extend({
        defaults: {
            template: "MoneyHash_Payment/payment/moneyhash-payment",
            cardElementsMap: {
                "card-holder-name": {
                    elementType: "cardHolderName",
                    placeholder: $.mage.__("Card Holder Name"),
                    validationKey: "card_holder_name",
                },
                "card-number": {
                    elementType: "cardNumber",
                    placeholder: $.mage.__("Card Number"),
                    validationKey: "card_number",
                },
                "card-expiry-month": {
                    elementType: "cardExpiryMonth",
                    placeholder: $.mage.__("MM"),
                    validationKey: "expiry_month",
                },
                "card-expiry-year": {
                    elementType: "cardExpiryYear",
                    placeholder: $.mage.__("YY"),
                    validationKey: "expiry_year",
                },
                "card-cvv": {
                    elementType: "cardCvv",
                    placeholder: $.mage.__("CVV"),
                    validationKey: "cvv",
                },
            },
            additionalFields: null,
        },

        // Observables
        intentId: ko.observable(false),
        selectedMethod: ko.observable(null),
        expressMethods: ko.observableArray([]),
        paymentMethods: ko.observableArray([]),

        // Lifecycle
        initialize: function () {
            this._super();
            this.config = window.checkoutConfig.payment[this.getCode()];
            if (this.config.is_test_mode) {
                window.MONEYHASH_IFRAME_URL = 'https://stg-embed.moneyhash.io';
                window.API_URL = 'https://staging-web.moneyhash.io';
                window.MONEYHASH_VAULT_INPUT_IFRAME_URL = 'https://vault-staging-form.moneyhash.io';
                window.MONEYHASH_VAULT_API_URL = 'https://vault-staging.moneyhash.io';
            }
            this.moneyHash = null;
            this.paymentStatus = null;
            this.state = null;
            this.stateDetails = null;
            this.intentDetails = null;
            this.additionalFields = new AdditionalFields();
            this.additionalFields.setParentComponent(this);
            this.selectMethodHandler = this.selectMethodHandler.bind(this);
            this.initSdk().then(() => this.loadMethods());

        },

        // Utilities
        handleError: function (context, error) {
            let message = error?.responseJSON?.message || $.mage.__('Error submitting data. Please try again.');
            const params = error?.responseJSON?.parameters || [];
            params.forEach((param, index) => {
                const placeholder = `%${index + 1}`;
                message = message.replace(placeholder, param);
            });
            messageList.addErrorMessage({message: message});
        },

        withLoader: async function (fn) {
            try {
                fullScreenLoader.startLoader();
                return await fn();
            } finally {
                fullScreenLoader.stopLoader();
            }
        },

        // SDK init
        initSdk: async function () {
            if (!window.MoneyHash || !window.MoneyHash.default) {
                console.error("MoneyHash SDK is not loaded.");
                return;
            }
            const HeadlessMoneyHash = window.MoneyHash.default;
            this.moneyHash = new HeadlessMoneyHash({
                type: "payment",
                publicApiKey: this.config.public_key,
                locale: this.config.language
            });
        },

        // Intent management
        createIntent: async function () {
            try {
                const id = await createIntentAction(this.getData(), this.messageContainer);
                this.intentId(id[0]);
                return id[0];
            } catch (error) {
                this.handleError("CreateIntent", error);
            }
        },
        updateInstanceState: function (response) {
            this.paymentStatus = response.paymentStatus?.status;
            this.state = response.state;
            this.stateDetails = response.stateDetails;
            this.intentDetails = response.intent;
            this.selectedMethod(response.selectedMethod);
            console.log("Updated state:", response);
            this.renderStateAction();
        },

        // State handling
        stateHandlers: {
            METHOD_SELECTION: "displayPaymentMethods",
            FORM_FIELDS: "displayCardForm",
            INTENT_FORM: "handleIntentForm",
            URL_TO_RENDER: "renderIframe",
            INTENT_PROCESSED: "showIntentStatus",
            INTENT_UNPROCESSED: "showIntentStatus",
            TRANSACTION_FAILED: "showIntentStatus",
        },

        renderStateAction: function () {
            const handler = this.stateHandlers[this.state];
            if (handler && typeof this[handler] === "function") {
                this[handler]();
            } else {
                alert("Unhandled state: " + this.state);
            }
        },
        handleIntentForm: async function () {
            var container = $('input[id="' + this.getMethodCode(this.selectedMethod()) + '"]').parents('.payment-method').find('.payment-method-content');
            container.empty();
            container.append('<div id="intent-form"></div>');
            await this.moneyHash.renderForm({
                selector: "#intent-form",
                intentId: this.intentId(),
                onHeightChange: height => {
                    document.querySelector("#intent-form").style.height = `${height}px`;
                },
            });
        },
        renderIframe: function () {
            if (this.stateDetails.renderStrategy === 'IFRAME' && this.paymentStatus !== "AUTHORIZE_ATTEMPT_PENDING") {

                var container = $('input[id="' + this.getMethodCode(this.selectedMethod()) + '"]').parents('.payment-method').find('.payment-method-content');
                container.empty();
                var iframe = $('<iframe>', {
                    id: 'payment-iframe',
                    src: this.stateDetails.url,
                    style: 'width:100%;height:600px;border:none;'
                });
                $('#payment-iframe').on('load', function () {
                    $('#payment-iframe').style.height = $('#payment-iframe').contentDocument.body.scrollHeight + 'px';
                });
                container.append(iframe);
            } else {
                window.location.href = this.stateDetails.url;
            }
        },
        resizeIframe: function (obj) {
            obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
        },
        // Methods
        loadMethods: async function () {
            await this.withLoader(async () => {
                await this.createIntent();
                if(!this.intentId())
                    return;
                try {
                    if (this.config.integration_type === "iframe") {
                        $('.mh-content').removeClass('hidden');
                        var iframe = $('<iframe>', {
                            id: 'payment-iframe',
                            src: this.config.embed_url + this.intentId() + "?lang=" + this.config.language.split("-")[0],
                            style: 'width:100%;height:600px;border:none;'
                        });
                        $('#payment-iframe').on('load', function () {
                            $('#payment-iframe').style.height = $('#payment-iframe').contentDocument.body.scrollHeight + 'px';
                        });
                        $('.mh-content').html(iframe);
                    } else {
                        const {expressMethods, paymentMethods} =
                            await this.getMethods({intentId: this.intentId()});

                        this.expressMethods(expressMethods);
                        this.paymentMethods(paymentMethods);
                        this.manageShownContent("methods");
                    }
                } catch (error) {
                    this.handleError("loadMethods", error);
                }
            });
        },

        displayPaymentMethods: async function () {
            await this.withLoader(async () => {
                try {
                    const {expressMethods, paymentMethods} = await this.getIntentMethods(
                        this.intentId()
                    );
                    this.expressMethods(expressMethods);
                    this.paymentMethods(paymentMethods);
                    this.manageShownContent("methods");
                } catch (error) {
                    this.handleError("displayPaymentMethods", error);
                }
            });
        },

        selectMethodHandler: async function ({id}) {
            if (!this.intentId() || this.selectedMethod() === 'CARD') {
                await this.createIntent();
            }

            await this.withLoader(async () => {
                try {
                    const response = await this.selectMethod({
                        methodId: id,
                        intentId: this.intentId(),
                    });
                    this.updateInstanceState(response);
                } catch (error) {
                    this.handleError("selectMethod", error);
                }
            });
            $(`input[name='payment[method]'][value='${this.getMethodCode(id)}']`).prop("checked", true).trigger("change");
        },

        displayCardForm: async function () {
            if (this.stateDetails?.formFields?.card?.accessToken !== undefined) {
                this.additionalFields.renderFields();
                await this.createCardFromElements();
                var container = $('input[id="' + this.getMethodCode(this.selectedMethod()) + '"]').parents('.payment-method').find('.payment-method-content');
                container.append($('#card-form'));
                $('#card-form').removeClass('hidden');
            } else {
                if (this.stateDetails?.formFields?.billing) {
                    this.additionalFields.renderFields(this.stateDetails?.formFields?.billing);
                }
                if (this.stateDetails?.formFields?.shipping) {
                    this.additionalFields.renderFields(this.stateDetails?.formFields?.shipping);
                }
            }
        },

        submitForm: async function () {
            await this.withLoader(async () => {
                if (this.selectedMethod() === "CARD") {
                    try {
                        this.resetCardFormErrors();
                        const response = await this.submitCardForm();
                        this.updateInstanceState(response);
                    } catch (error) {
                        this.handleError("submitForm", error);
                        this.renderCardFormErrors(error);
                    }
                } else {
                    try {
                        const response = await this.moneyHash.submitForm({
                            intentId: this.intentId(),
                            paymentMethod: this.selectedMethod(),
                            billingData: this.additionalFields.getFieldValues()
                        });
                        this.updateInstanceState(response);
                    } catch (error) {
                        this.additionalFields.changeIsPlaceOrderActionAllowed();
                        console.log(error);
                    }
                }
            });
        },

        showIntentStatus: function () {
            const intentStatus = this.intentDetails?.status;
            const intentStatusMessage = document.getElementById("intent-status-message");

            switch (intentStatus) {
                case "PROCESSED":
                    intentStatusMessage.textContent = "Payment processed successfully!";
                    this.placeOrder();
                    break;
                case "UNPROCESSED":
                    intentStatusMessage.textContent = "Payment failed.";
                    break;
                default:
                    intentStatusMessage.textContent = "Unknown intent status.";
            }

            this.manageShownContent("intent-status");
        },

        // Card form helpers
        resetCardFormErrors: function () {
            Object.keys(this.cardElementsMap).forEach((field) => this.setFieldError(field, null));
        },

        renderCardFormErrors: function (errors) {
            Object.keys(this.cardElementsMap).forEach((field) => {
                const key = this.cardElementsMap[field].validationKey;
                this.setFieldError(field, errors[key] || null);
            });
        },

        setFieldError: function (field, message) {
            const errorElement = document.getElementById(`${field}-error`);
            if (!errorElement) return;
            if (message) {
                errorElement.textContent = message;
                errorElement.classList.remove("invisible");
            } else {
                errorElement.textContent = "";
                errorElement.classList.add("invisible");
            }
        },

        manageShownContent: function (contentId) {
            document.querySelectorAll(".mh-content").forEach((c) => c.classList.add("hidden"));
            const activeContent = document.getElementById(contentId);
            if (activeContent) {
                activeContent.classList.remove("hidden");
            } else {
                console.warn(`Content with ID ${contentId} not found.`);
            }
        },

        // SDK wrappers
        getIntentDetails: async function (intentId) {
            try {
                return await this.moneyHash.getIntentDetails(intentId);
            } catch (error) {
                this.handleError("getIntentDetails", error);
                throw error;
            }
        },

        getMethods: async function (payload) {
            try {
                return await this.moneyHash.getMethods(payload);
            } catch (error) {
                this.handleError("getMethods", error);
                throw error;
            }
        },

        getIntentMethods: async function (intentId) {
            try {
                return await this.moneyHash.getIntentMethods(intentId);
            } catch (error) {
                this.handleError("getIntentMethods", error);
                throw error;
            }
        },

        selectMethod: async function ({methodId, intentId}) {
            try {
                return await this.moneyHash.proceedWith({
                    type: "method",
                    id: methodId,
                    intentId,
                });
            } catch (error) {
                this.handleError("selectMethod", error);
                throw error;
            }
        },

        createCardFromElements: async function () {
            const elements = await this.moneyHash.elements({
                styles: {
                    color: {error: "#f00"},
                    placeholderColor: "grey",
                    height: "40px",
                    padding: "8px",
                },
            });

            Object.keys(this.cardElementsMap).forEach((selector) => {
                const config = this.cardElementsMap[selector];
                elements
                    .create({
                        elementType: config.elementType,
                        elementOptions: {
                            selector: `#${selector}`,
                            placeholder: config.placeholder,
                        },
                    })
                    .mount();
            });
        },

        submitCardForm: async function () {
            const cardData = await this.moneyHash.cardForm.collect();
            console.log("card data", cardData);
            return await this.moneyHash.cardForm.pay({
                intentId: this.intentId(),
                cardData
            });
            /*return await this.moneyHash.submitForm({
                intentId: this.intentId(),
                accessToken: this.stateDetails?.formFields?.card?.accessToken,
            });*/
        },

        // Magento checkout helpers
        getData: function () {
            const data = this._super();
            data.additional_data = {
                moneyhash_pay_data: JSON.stringify({
                    selectedMethod: this.selectedMethod(),
                    intentId: this.intentId(),
                }),
            };
            return data;
        },

        getMethodCode: function (methodCode) {
            return this.getCode() + "_" + methodCode;
        },
        isMethodChecked: function (methodCode) {
            return methodCode === this.selectedMethod();
        },
        hasAdditionalFields: function () {
            console.log("visiblity", this.additionalFields.visible())
            return this.additionalFields.visible();
        },

        /*
         * Get additional fields component
         */
        getAdditionalFields: function () {
            return this.additionalFields;
        }
    });
});
