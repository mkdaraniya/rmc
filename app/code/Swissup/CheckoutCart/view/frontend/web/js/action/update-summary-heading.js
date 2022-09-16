define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    return function (totalQty) {
        var spansArr = $('.opc-block-summary .items-in-cart .title strong span');

        //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
        // used when navigating between checkout steps
        window.checkoutConfig.totalsData.items_qty = totalQty;
        //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

        if (spansArr.length === 2) {
            // [x, items in cart]
            $(spansArr[0]).html(totalQty);
            $(spansArr[1]).html(
                totalQty === 1 ? $t('Item in Cart') : $t('Items in Cart')
            );
        } else if (spansArr.length === 4) {
            // [10, of, x, items in cart]
            $(spansArr[2]).html(totalQty);
        }
    };
});
