define([
    'Magento_Ui/js/lib/view/utils/async',
    'Magento_Checkout/js/model/quote'
], function ($, quote) {
    'use strict';

    var copyRules = {
            '.table-totals .grand.totals': '.fc-order-summary-copy .table-totals'
        };

    /**
     * Copy elements
     */
    function copyOrderElements() {
        $.each(copyRules, function (from, to) {
            $(to).children().remove();
            $('.opc-block-summary').find(from)
                .clone(true)
                .removeClass('fc-hidden')
                .appendTo(to);
        });
    }

    return {
        /**
         * Plugin initialization
         */
        init: function () {
            $(document.body).addClass('fc-order-below-payment');
            this.createContainer();
            this.observer();
        },

        /**
         * Create container where the content will be placed
         */
        createContainer: function () {
            $.async('#payment', function (el) {
                $(el).after([
                    '<li class="fc-order-summary-copy">',
                        '<table class="table-totals"></table>',
                        '<div class="fc-place-order-button"></div>',
                    '</li>'
                ].join(''));
            });
        },

        /**
         * Attach observers
         */
        observer: function () {
            $.async('.opc-block-summary .place-order', function () {
                copyOrderElements();
                $('.opc-block-summary').find('.place-order.order-review-form')
                    .clone(true)
                    .removeClass('fc-hidden')
                    .appendTo('.fc-order-summary-copy .fc-place-order-button');
            });

            quote.totals.subscribe(function () {
                setTimeout(copyOrderElements, 200);
            });
        }
    };
});
