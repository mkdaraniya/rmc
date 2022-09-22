define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'mage/collapsible'
], function ($, _) {
    'use strict';

    var defaults = {
        header: '> :eq(0)',
        content: false, // next to the header item will be selected
        openedState: '_active'
    };

    /**
     * @param {Element} el
     * @param {Object} config
     */
    function init(el, config) {
        var settings = $.extend({}, defaults, config),
            header = $(el).find(settings.header),
            span = $(header).find(' > span');

        if (!header.length || $(el).data('mageCollapsible')) {
            // collabsible is already applied
            return;
        }

        if (!span.length) {
            $(header).wrapInner('<span></span>');
            span = $(header).find(' > span');
        }

        if (!span.find('span').length) {
            $(span)
                .wrapInner('<span></span>') // Luma's markup
                .append('\n');              // Fix for "display: inline" caused spacing
        }

        $(span).addClass('action action-toggle'); // Luma's markup

        $(el).collapsible(settings);
    }

    /**
     * Create collabsible element and add required markup (Based on Luma's requirements)
     *
     * @param  {String|Element} el
     * @param  {Object} config
     */
    return function (el, config) {
        if (typeof el === 'string') {
            $.async(el, function (element) {
                if (!config || !config.header) {
                    _.delay(init, 100, element, config);
                } else {
                    $.async([el, config.header].join(' '), function () {
                        _.delay(init, 100, element, config);
                    });
                }
            });
        } else {
            init(el, config);
        }
    };
});
