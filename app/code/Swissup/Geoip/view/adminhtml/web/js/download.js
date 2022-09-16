define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, alert, $t) {
    'use strict';

    return function (config, el) {
        $(el).on('click', function () {
            $.ajax({
                method: 'POST',
                url: config.url,
                showLoader: true,
                dataType: 'json',
                data: {
                    //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    form_key: window.FORM_KEY,
                    //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                    license: $(config.licenseKeyField).val(),
                    edition: $(config.editionField).val()
                }
            })
            .done(function (data) {
                if (data.error) {
                    return alert({
                        title: $t('Error'),
                        content: data.message
                    });
                }
                $('#save').trigger('click');
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                alert({
                    title: $t('Error'),
                    content: errorThrown
                });
            });
        });
    }
});
