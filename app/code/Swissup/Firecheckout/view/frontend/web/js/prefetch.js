define([
    'Magento_Customer/js/customer-data'
], function (customerData) {
    'use strict';

    var cart = customerData.get('cart'),
        link = document.createElement('link'),
        prefetchedDate = window.localStorage.getItem('fc-prefetched'),
        prefetched = false,
        urls = [];

    if (prefetchedDate) {
        prefetched = Date.now() - prefetchedDate < 10 * 60 * 1000; // 10 minutes ago
    }

    if (prefetched ||
        !window.checkout ||
        !window.checkout.checkoutUrl ||
        !link.relList ||
        !link.relList.supports ||
        !link.relList.supports('prefetch')
    ) {
        return;
    }

    urls.push(require.toUrl('Swissup_Firecheckout/fcbuild-all.js'));

    /** [prefetch description] */
    function prefetch() {
        urls.forEach(function (url) {
            link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = url;
            document.head.appendChild(link);
        });
    }

    cart.subscribe(function (data) {
        if (!data.summary_count || prefetched) {
            return;
        }

        prefetched = true;
        window.localStorage.setItem('fc-prefetched', Date.now());

        prefetch();
    });
});
