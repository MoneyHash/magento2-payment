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

define([
    'ko',
    'uiComponent',
    'jquery'
], function (ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MoneyHash_Payment/payment/moneyhash-additional-fields',
            fields: ko.observableArray([]),
            visible: ko.observable(false),
            parentComponent: null,
            isPlaceOrderActionAllowed: ko.observable(true)
        },

        initialize: function () {
            this._super();
            this.initFields();
            return this;
        },
        setParentComponent: function (parent) {
            this.parentComponent = parent;
        },
        changeIsPlaceOrderActionAllowed: function ()
        {
            this.isPlaceOrderActionAllowed(!this.isPlaceOrderActionAllowed());
        },
        initFields: function () {
            // Initialize with empty fields
            this.fields([]);
        },

        /**
         * Render form fields based on state details
         */
        renderFields: function (fields) {
            if (!fields || !Array.isArray(fields)) {
                this.fields([]);
                this.visible(false);
                return;
            }

            fields = fields.map(field => {
                return {
                    type: field.type || 'text',
                    name: field.name,
                    label: field.label,
                    hint: field.hint,
                    value: ko.observable(field.value || ''),
                    readOnly: field.readOnly || false,
                    validation: field.validation || { required: false },
                    dependsOn: field.dependsOn,
                    optionsList: field.optionsList || [],
                    optionsMap: field.optionsMap || {},
                    error: ko.observable(''),
                    hasError: ko.observable(false)
                };
            });

            this.fields(fields);
            this.visible(true);
        },

        /**
         * Hide the form fields
         */
        hideFields: function () {
            this.visible(false);
            this.fields([]);
        },

        /**
         * Validate all fields
         */
        validateForm: function () {
            let isValid = true;
            const fields = this.fields();

            fields.forEach(field => {
                const value = field.value().trim();
                const validation = field.validation;

                field.hasError(false);
                field.error('');

                // Required validation
                if (validation.required && !value) {
                    field.hasError(true);
                    field.error('This field is required');
                    isValid = false;
                    return;
                }

                // Min length validation
                if (validation.minLength && value.length < validation.minLength) {
                    field.hasError(true);
                    field.error(`Minimum ${validation.minLength} characters required`);
                    isValid = false;
                    return;
                }

                // Max length validation
                if (validation.maxLength && value.length > validation.maxLength) {
                    field.hasError(true);
                    field.error(`Maximum ${validation.maxLength} characters allowed`);
                    isValid = false;
                    return;
                }

                // Type-specific validation
                if (!this.validateFieldByType(field)) {
                    isValid = false;
                }
            });

            return isValid;
        },

        /**
         * Validate field based on type
         */
        validateFieldByType: function (field) {
            const value = field.value().trim();
            if (!value && !field.validation.required) {
                return true; // Skip validation if empty and not required
            }

            switch (field.type) {
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        field.hasError(true);
                        field.error('Please enter a valid email address');
                        return false;
                    }
                    break;

                case 'number':
                    if (isNaN(value) || value === '') {
                        field.hasError(true);
                        field.error('Please enter a valid number');
                        return false;
                    }
                    break;

                case 'phoneNumber':
                    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
                    if (!phoneRegex.test(value.replace(/[\s\-\(\)]/g, ''))) {
                        field.hasError(true);
                        field.error('Please enter a valid phone number');
                        return false;
                    }
                    break;

                case 'date':
                    if (isNaN(Date.parse(value))) {
                        field.hasError(true);
                        field.error('Please enter a valid date');
                        return false;
                    }
                    break;
            }

            return true;
        },

        /**
         * Get field CSS classes
         */
        getFieldClasses: function (field) {
            let classes = 'field moneyhash-field moneyhash-field-' + field.type;

            if (field.hasError()) {
                classes += ' error';
            }

            if (field.validation.required) {
                classes += ' required';
            }

            return classes;
        },

        /**
         * Get input CSS classes
         */
        getInputClasses: function (field) {
            let classes = 'input-text';

            if (field.hasError()) {
                classes += ' validation-failed';
            }

            if (field.readOnly) {
                classes += ' readonly';
            }

            return classes;
        },

        /**
         * Handle field value change for dependent fields
         */
        onFieldChange: function (field, event) {
            this.updateDependentFields(field.name);
        },

        /**
         * Update fields that depend on this field
         */
        updateDependentFields: function (fieldName) {
            const fields = this.fields();

            fields.forEach(field => {
                if (field.dependsOn === fieldName) {
                    // Reset dependent field value when parent changes
                    field.value('');
                    field.hasError(false);
                    field.error('');
                }
            });
        },

        /**
         * Get options for select field based on dependencies
         */
        getSelectOptions: function (field) {
            if (!field.dependsOn) {
                return field.optionsList;
            }

            const parentField = this.fields().find(f => f.name === field.dependsOn);
            if (!parentField || !field.optionsMap) {
                return field.optionsList;
            }

            const parentValue = parentField.value();
            return field.optionsMap[parentValue] || [];
        },

        /**
         * Check if field should be visible (for conditional fields)
         */
        isFieldVisible: function (field) {
            if (!field.dependsOn) {
                return true;
            }

            const parentField = this.fields().find(f => f.name === field.dependsOn);
            if (!parentField) {
                return true;
            }

            const parentValue = parentField.value();
            return !!(field.optionsMap && field.optionsMap[parentValue]);
        },

        /**
         * Get all field values as an object
         */
        getFieldValues: function () {
            const values = {};
            this.fields().forEach(field => {
                values[field.name] = field.value();
            });
            return values;
        },

        /**
         * Clear all field values
         */
        clearFields: function () {
            this.fields().forEach(field => {
                field.value('');
                field.hasError(false);
                field.error('');
            });
        },
        placeOrderFromFields: function ()
        {
            if (!this.validateForm()) {
                return false;
            }
            this.isPlaceOrderActionAllowed(false);
            this.parentComponent.submitForm();
        }
    });
});
