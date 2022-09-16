define([
    'jquery',
    'uiComponent',
    'uiRegistry',
    "Magento_Ui/js/modal/alert",
    'Swissup_Checkout/js/scroll-to-error',
    'ko',
    'mage/translate',
    'mage/validation',
    'domReady!'
], function ($, Component, registry, alert, scrollToError, ko) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Swissup_CheckoutFields/order/view',
            listens: {
                '${ $.provider }:swissupCheckoutFields': 'fieldsChanged'
            }
        },

        initialize: function() {
            this._super();

            this.editMode = ko.observable(false);
            this.fields = ko.observableArray([]);
            this.initListeners();
            $('.order-addresses').after($('.order-view-swissup-fields'));
        },

        initElement: function (el) {
            if (el.input_type === 'input') {
                this.fields.push({
                    'id': el.id,
                    'label': el.label,
                    'multiline': el.multiple || el.rows,
                    'value': ko.observable(this.getFieldValue(el))
                });
            }

            return this._super();
        },

        getFieldValue: function(el) {
            var fieldValue, optionsValue, option;

            fieldValue = el.value();
            if ((fieldValue || fieldValue === 0) && typeof el.getOption === 'function') {
                if (el.multiple) {
                    optionsValue = [];
                    $.each(fieldValue, function(i, val) {
                        optionsValue.push(el.getOption(val).label);
                    });
                    fieldValue = optionsValue.join('\n');
                } else {
                    fieldValue = el.getOption(fieldValue).label;
                }
            } else if (typeof fieldValue === 'boolean' && el.options) {
                fieldValue = fieldValue ? 1 : 0;
                $.each(el.options, function (i, item) {
                    if (item.value === fieldValue) {
                        option = item;
                        return false
                    }
                });

                if (option) {
                    fieldValue = option.label;
                }
            }

            return fieldValue;
        },

        fieldsChanged: function() {
            var field;

            if (this.fields().length === 0) return;

            $.each(this.fields(), function(i, el) {
                field = registry.get('index = ' + el.id);
                el.value(this.getFieldValue(field));
            }.bind(this));
        },

        initListeners: function() {
            $('#checkout-fields-edit-link').on('click', function() {
                this.toggleEdit();
                return false;
            }.bind(this));

            registry.get('index = checkoutfields-save-button', function(item) {
                item.action = $.proxy(this.saveFields, this, item.url);
            }.bind(this));

            registry.get('index = checkoutfields-cancel-button', function(item) {
                item.action = $.proxy(this.closeEdit, this);
            }.bind(this));
        },

        saveFields: function(saveUrl) {
            var provider = registry.get(this.provider),
                data = provider.swissupCheckoutFields;

            $.each(data, function(key, value) {
                if (value === undefined ||
                   typeof value === 'object' && value.length === 0
                ) {
                    data[key] = null;
                } else if (typeof value === 'boolean') {
                    data[key] = value ? 1 : 0;
                }
            });

            if (this.validateFields(provider)) {
                $.post({
                    url: saveUrl,
                    dataType: 'json',
                    showLoader: true,
                    data: data
                })
                .done($.proxy(this.onSaveSuccess, this))
                .fail($.proxy(this.onSaveFail, this));
            }
        },

        validateFields: function(provider) {
            var result;

            provider.set('params.invalid', false);
            provider.trigger('swissupCheckoutFields.data.validate');
            result = !provider.get('params.invalid');

            if (!result) {
                scrollToError();
            }

            return result;
        },

        onSaveSuccess: function(result) {
            if (result.error) {
                this.onSaveFail(result.message);
            } else {
                this.saveState();
                this.toggleEdit();
                this.scrollToFields();
            }
        },

        onSaveFail: function(message) {
            if (!message) {
                message = $.mage.__(
                    'Error saving checkout fields. Please review the log for details.'
                );
            }
            alert({content: message});
        },

        closeEdit: function() {
            this.resetState();
            this.toggleEdit();
            this.scrollToFields();
        },

        resetState: function() {
            $.each(this.elems(), function(i, el) {
                if (el.input_type === 'input') {
                    el.reset();
                }
            });
        },

        saveState: function() {
            $.each(this.elems(), function(i, el) {
                if (el.input_type === 'input') {
                    if (el.multiple) {
                        el.initLinks();
                    }
                    el.overload();
                }
            });
        },

        toggleEdit: function() {
            this.editMode(!this.editMode());
        },

        scrollToFields: function() {
            $([document.documentElement, document.body]).animate({
                scrollTop: $(".order-view-swissup-fields").offset().top - $('.page-actions._fixed').outerHeight()
            }, 500);
        }
    });
});
