define(
    [
        'jquery',
        'underscore',
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/customer-data',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/action/create-billing-address',
        'Magento_Checkout/js/action/select-billing-address',
        'mage/translate',
        'iwdOpcHelper'
    ],
    function ($,
              _,
              Component,
              ko,
              checkoutData,
              quote,
              fullScreenLoader,
              customer,
              customerData,
              addressList,
              createBillingAddress,
              selectBillingAddress,
              $t) {
        'use strict';

        $(document).on('focus','input',function (e) {
            $(this).closest('.control').addClass('focus');
        })

        $(document).on('focusout','input',function (e) {
            let val = $(this).val();
            if (!val || val.length === 0) {
                $(this).closest('.control').removeClass('focus');
            }
        })

        var observedElements = [],
            setBillingActionTimeout = 0,
            inlineAddress = "",
            newAddressOption = {
                /**
                 * Get new address label
                 * @returns {String}
                 */
                getAddressInline: function () {
                    return $t('New Address');
                },
                customerAddressId: null
            },
            countryData = customerData.get('directory-data'),
            addressOptions = addressList().filter(function (address) {
                var isDublicate = inlineAddress === address.getAddressInline();
                inlineAddress = address.getAddressInline();
                return address.getType() === 'customer-address' && !isDublicate;
            });

        addressOptions.push(newAddressOption);

        return Component.extend({
            defaults: {
                template: 'IWD_Opc/billing-step-virtual'
            },
            isShowComment: quote.isShowComment(),
            commentValue: ko.observable(checkoutData.getComment()),
            quoteIsVirtual: quote.isVirtual(),
            checkoutData: window.checkoutData,

            currentBillingAddress: quote.billingAddress,
            addressOptions: addressOptions,
            isCustomerLoggedIn: customer.isLoggedIn,
            customerEmail: quote.guestEmail ? quote.guestEmail : window.checkoutConfig.customerData.email,
            customerHasAddresses: addressOptions.length > 1,
            logoutUrl: quote.getLogoutUrl(),
            selectedAddress: ko.observable(null),

            screenResize: function () {
                let self = this;

                window.addEventListener('resize', function (e) {
                    self.multiStepEventListener();
                });
            },

            multiStepEventListener: function () {
                let self = this,
                    screen = window.screen;

                if (screen.width > 991) {
                    self.updateMultiStepResolution(self.isDesktopMultiResolution());
                } else if (screen.width <= 991 & screen.width > 575) {
                    self.updateMultiStepResolution(self.isTabletMultiResolution());
                } else {
                    self.updateMultiStepResolution(self.isMobileMultiResolution());
                }
            },

            updateMultiStepResolution: function (resolution) {
                let self = this;

                let paymentInterval = setInterval(function (){
                    let payment = self.checkoutData.payment;

                    if (typeof payment !== 'undefined') {
                        if (self.AddressStep()) {
                            self.CurrentStep(1);
                        } else {
                            self.CurrentStep(3);
                        }

                        if (resolution == 'multistep') {
                            self.isMultiStepResolution(true);
                            payment.isMultiStepResolution(true);

                            if (self.CurrentStep() == 1) {
                                self.AddressStep(true);
                                payment.PaymentStep(false);
                            } else {
                                self.AddressStep(false);
                                payment.PaymentStep(true);
                            }

                        } else {
                            self.isMultiStepResolution(false);
                            payment.isMultiStepResolution(false);
                            self.AddressStep(true);
                            payment.PaymentStep(true);
                        }
                        clearInterval(paymentInterval);
                        clearTimeout(paymentTimeout);
                    }
                },500);

                let paymentTimeout = setTimeout(function (){
                    clearInterval(paymentInterval);
                },5000)

                return true;
            },

            startLoader: function(){
                fullScreenLoader.startLoader();
            },

            stopLoader: function(timeout = 2000){
                setTimeout(function () {
                    fullScreenLoader.stopLoader();
                },timeout)
            },

            manageDeliveryComment: function() {
                if(this.isCommentVisible()) {
                    this.isCommentVisible(false);
                } else {
                    this.isCommentVisible(true);
                }
                return true;
            },

            isEmpty: function (value) {
                return (!value || value.length === 0);
            },

            updateFocusControl: function () {
                let self = this;

                if ($('.field .control.focus').length) {
                    $('.field .control.focus').each(function () {
                        let input = $(this).find('input');
                        if (self.isEmpty(input.val())) {
                            $(this).removeClass('focus');
                        }
                    });
                }
            },

            autoFill: function () {
                let self = this;

                $(document).on('blur','input',function (){
                    if (!self.isEmpty($(this).val())) {
                        $(this).closest('.control').addClass('focus');
                    } else {
                        $(this).closest('.control').removeClass('focus');
                    }
                });
            },

            setDesignResolution: function() {
                if (this.checkoutData.layout.desktop == 'multistep') {
                    this.isMultiStepResolution(true);
                } else {
                    this.isMultiStepResolution(false);
                }
            },

            goToShoppingCart: function() {
                this.startLoader();
                window.location.href = window.location.origin + '/checkout/cart/';
            },

            goToPaymentStep: function() {
                let self = this,
                    payment = self.checkoutData.payment,
                    billing = self.checkoutData.billing;

                self.startLoader();

                if(!billing.billingStepVirtualValidate()){
                    return false;
                }

                self.AddressStep(false);
                payment.PaymentStep(true);

                self.CurrentStep(2);
                this.stopLoader(500);

                return true;
            },

            initialize: function () {
                let self = this;

                self._super().observe({
                    isAddressFormVisible: ko.observable(true),
                    AddressStep: ko.observable(true),
                    DeliveryStep: ko.observable(true),
                    CurrentStep: 1,
                    isCommentVisible: false,
                    isMultiStepResolution: ko.observable(false),
                    isDesktopMultiResolution: ko.observable(false),
                    isTabletMultiResolution: ko.observable(false),
                    isMobileMultiResolution: ko.observable(false),
                });

                self.checkoutData.billingStepVirtual = this;

                self.commentValue.subscribe(function (value) {
                    checkoutData.setComment(value);
                });

                quote.billingAddress.subscribe(function (address) {
                        self.updateFocusControl();
                })

                self.isDesktopMultiResolution(self.checkoutData.layout.desktop);
                self.isTabletMultiResolution(self.checkoutData.layout.tablet);
                self.isMobileMultiResolution(self.checkoutData.layout.mobile);

                self.autoFill();
                self.multiStepEventListener();
                self.screenResize();
            },

            textareaAutoSize: function (element) {
                $(element).textareaAutoSize();
            }
        });
    }
);
