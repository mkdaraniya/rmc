/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MultiStoreViewPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return function (Component) {
        return Component.extend({

            /**
             * @override
             */
            initialize: function () {
                var cartData = customerData.get('cart');

                if (window.checkout && cartData().storeId !== window.checkout.storeId) {
                    customerData.reload(['wishlist'], false);
                }

                return this._super();
            },
        });
    }
});
