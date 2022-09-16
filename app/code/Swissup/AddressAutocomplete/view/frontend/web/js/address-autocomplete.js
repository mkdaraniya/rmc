/* global google */
define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'uiComponent',
    'Swissup_AddressAutocomplete/js/google-maps',
    'Magento_Customer/js/customer-data'
], function (
    $,
    _,
    Component,
    maps,
    customerData
) {
    'use strict';

    var countryData,
        config,
        authFailed = false,
        patterns = {
            street: '{{route.long_name}}',
            streetNumber: '{{street_number.short_name}}',
            unitNumber: '{{subpremise.short_name}}',
            countryCode: '{{country.short_name}}',
            city: '{{' +
                [
                    'postal_town.long_name',
                    'locality.long_name',
                    'administrative_area_level_2.long_name',
                    'sublocality_level_1.long_name'
                ].join('|') +
            '}}',
            postcode: '{{postal_code.short_name}}{{-}}{{postal_code_suffix.short_name}}',
            regionName: '{{administrative_area_level_1.long_name}}',
            regionCode: '{{administrative_area_level_1.short_name}}'
        };

    countryData = customerData.get('directory-data');

    if (_.isEmpty(countryData())) {
        customerData.reload(['directory-data'], false);
    }

    /**
     * @return {Object}
     */
    function getAddressMapping() {
        var mapping = {
                'country_id': patterns.countryCode,
                'street1': patterns.street,
                'street2': '',
                'street3': '',
                'street4': '',
                'city': patterns.city,
                'postcode': patterns.postcode,
                'region': patterns.regionName,
                'region_code': patterns.regionCode
            },
            streetNumberPattern = patterns.streetNumber;

        if (config.unitNumber.placement === 'street_number_start') {
            streetNumberPattern = patterns.unitNumber + config.unitNumber.divider + streetNumberPattern;
        } else if (config.unitNumber.placement === 'street_number_end') {
            streetNumberPattern += config.unitNumber.divider + patterns.unitNumber;
        }

        if (config.streetNumberPlacement === 'line1_start') {
            mapping.street1 = streetNumberPattern + '{{ }}' + mapping.street1;
        } else if (config.streetNumberPlacement === 'line1_end') {
            mapping.street1 += '{{ }}' + streetNumberPattern;
        } else if (config.streetNumberPlacement === 'line2') {
            mapping.street2 = streetNumberPattern;
        } else if (config.streetNumberPlacement.indexOf('custom_attributes[') === 0) {
            mapping[config.streetNumberPlacement] = streetNumberPattern;
        }

        if (config.unitNumber.placement === 'line1_start') {
            mapping.street1 = patterns.unitNumber + config.unitNumber.divider + mapping.street1;
        } else if (config.unitNumber.placement === 'line1_end') {
            mapping.street1 += config.unitNumber.divider + patterns.unitNumber;
        } else if (config.unitNumber === 'line2') {
            mapping.street2 += patterns.unitNumber + config.unitNumber.divider + mapping.street2;
        }

        return mapping;
    }

    /**
     * @param {String} country
     * @return {Object}
     */
    function getAdvancedAddressMapping(country) {
        var mapping = config.mapping['*'] || {};

        if (country) {
            mapping = _.extend(mapping, config.mapping[country] || {});
        }

        return mapping;
    }

    /**
     *
     * @param  {Object} el
     * @return {Object}
     */
    function getAutocomplete(el) {
        return el.addressAutocomplete;
    }

    /**
     * @param {Object} el
     * @return {jQuery}
     */
    function getClosestForm(el) {
        var form = $(el).closest('.address');

        if (!form.length) {
            form = $(el).closest('form');
        }

        return form;
    }

    /**
     * Find region_id by it's code, or name
     *
     * @param  {Object} address
     * @return {Number|Boolean}
     */
    function findRegionId(address) {
        //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
        var id,
            regions,
            regionCode = address.region_code,
            regionName = address.region,
            countryCode = address.country_id;
        //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

        if (!countryData()[countryCode] ||
            !countryData()[countryCode].regions
        ) {
            return false;
        }

        regions = countryData()[countryCode].regions;

        // 1. search by codes
        for (id in regions) {
            if (regions[id].code === regionCode) {
                return id;
            }
        }

        // 2. search by name
        for (id in regions) {
            if (regions[id].name === regionName) {
                return id;
            }
        }

        return false;
    }

    /**
     *
     * @param  {String} name
     * @param  {Int} value
     * @param  {Object} place
     * @return {String}
     */
    function extractFieldValueFromPlace(name, value, place) {
        var i = 0,
            field;

        //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
        while (place.address_components[i]) {
            field = place.address_components[i];

            if (field.types[0] === name) {
                return field[value];
            }

            i++;
        }
        //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

        return '';
    }

    /**
     * @param  {Object} place - @see autocomplete.getPlace()
     * @return {Object|Boolean}
     */
    function extractAddress(place, formattedAddress) {
        var mapping = _.extend(
                getAddressMapping(),
                getAdvancedAddressMapping(extractFieldValueFromPlace('country', 'short_name', place))
            ),
            separator = '|separator|',
            subpremise = formattedAddress.match(/\d+\/\d+/),
            streetNumber,
            address = {};

        //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
        if (!place || !place.address_components) {
            return false;
        }
        //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

        if (subpremise) {
            streetNumber = extractFieldValueFromPlace('street_number', 'short_name', place);

            if (streetNumber) {
                subpremise = subpremise[0].replace(streetNumber, '');
                subpremise = subpremise.replace('/', '');
            } else {
                subpremise = false;
            }
        }

        _.each(mapping, function (placeholder, key) {
            var re, matches;

            if (!placeholder.length) {
                address[key] = '';

                return;
            }

            address[key] = [];
            re = /(\{\{(.+?)\}\})/g;

            while ((matches = re.exec(placeholder)) !== null) {
                _.find(matches[2].split('|'), function (string) {
                    var field, value, fieldValue;

                    if (string.indexOf('.') === -1 || string.indexOf(' ') > -1) {
                        fieldValue = separator + string;
                    } else {
                        field = string.split('.')[0];
                        value = string.split('.')[1];
                        fieldValue = extractFieldValueFromPlace(field, value, place);

                        if (!fieldValue && field === 'subpremise' && subpremise) {
                            fieldValue = subpremise;
                        }
                    }

                    if (fieldValue) {
                        address[key].push(fieldValue);

                        return true;
                    }
                });
            }

            address[key] = address[key].filter(function (value, index) {
                if (value.indexOf(separator) === 0) {
                    return address[key][index - 1] && address[key][index + 1];
                }

                return true;
            });

            address[key] = address[key].map(function (value) {
                return value.replace(separator, '');
            });

            address[key] = address[key].join('');
        });

        //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
        address.street = [address.street1, address.street2, address.street3, address.street4];
        address.region_id = findRegionId(address);
        //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

        return address;
    }

    /**
     *
     * @param {Object} address
     * @param {jQuery} form
     */
    function setAddress(address, form) {
        var mapping = {
                'street1': '[name="street[0]"],#street_1',
                'street2': '[name="street[1]"],#street_2',
                'street3': '[name="street[2]"],#street_3',
                'street4': '[name="street[3]"],#street_4'
            },
            silent = form.is(':hidden'),
            els = $();

        _.each(address, function (value, key) {
            var selector = mapping[key] ? mapping[key] : '[name="' + key + '"]',
                el = $(selector, form);

            if (!el.length || typeof address[key] == 'undefined') {
                return;
            }

            if (el.is('select') && !el.find('option[value=' + value + ']').length) {
                return;
            }

            el.val(value);

            if (!silent) {
                el.trigger('change');
            } else if (value) {
                els = els.add(el);
            }
        });

        if (silent && !form.data('swa-trigger-change-timer-id')) {
            form.data('swa-trigger-change-timer-id', setInterval(function () {
                if (form.is(':hidden')) {
                    return;
                }

                clearInterval(form.data('swa-trigger-change-timer-id'));
                els.trigger('change');
            }, 1000));
        }
    }

    /**
     * @param  {Element} el
     */
    function placeChangedHandler(el) {
        var form, address;

        // 1. Match parent container
        form = getClosestForm(el);

        if (!form.length) {
            return;
        }

        // 2. Extract address from google place
        address = extractAddress(getAutocomplete(el).getPlace(), $(el).val());

        if (!address) {
            return;
        }

        // 3. Fill the fields inside parent container
        setAddress(address, form);
    }

    /**
     * @param {Element} el
     * @param {String} country
     */
    function restrict(el, country) {
        var autocomplete = getAutocomplete(el);

        if (!autocomplete) {
            return;
        }

        autocomplete.setComponentRestrictions({
            country: country
        });
    }

    /**
     * @param {Element} el
     */
    function initCountryRestrictions(el, selectors) {
        var form = getClosestForm(el);

        if (!config.restrictToCurrentCountry || !form.length) {
            return;
        }

        $.async(selectors, form.get(0), function (countryEl) {
            restrict(el, $(countryEl).val());
            $(countryEl).change(function () {
                restrict(el, $(this).val());
            });
        });
    }

    /**
     * Fill form with geolocation values
     *
     * @return $.Deferred
     */
    function initGeolocation() {
        var result = $.Deferred();

        if (!config.geolocation || $(config.selectors).val() || !navigator.geolocation) {
            result.resolve();

            return result;
        }

        navigator.geolocation.getCurrentPosition(function (position) {
            var latLng = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };

            (new google.maps.Geocoder()).geocode({
                'location': latLng
            }, function (results, status) {
                var address;

                if (status !== 'OK' || !results[0]) {
                    result.resolve();

                    return;
                }

                address = extractAddress(results[0], results[0].formatted_address || '');

                if (!address) {
                    result.resolve();

                    return;
                }

                // eslint-disable-next-line max-nested-callbacks
                $('[name="firstname"]').each(function (i, el) {
                    var form = getClosestForm(el);

                    if (!form.find(config.selectors).val()) {
                        setAddress(address, form);
                    }
                });

                result.resolve();
            });
        }, function () {
            result.resolve();
        });

        return result;
    }

    return Component.extend({
        /**
         * Component initializing
         */
        initialize: function () {
            this._super();

            config = this.settings;

            maps.auth().fail(this.failure.bind(this));
            maps.init(config.apiKey, config.locale)
                .done(this.success.bind(this));
        },

        /**
         * Revert field attributes if API key is invalid
         */
        failure: function () {
            var self = this;

            authFailed = true;

            $(config.fields).each(function () {
                self.destroyAutocomplete(this);
            });
        },

        /**
         * Method to run after maps API are loaded
         */
        success: function () {
            var self = this,
                done = false;

            // Give some time to initialize field values and placeholders at firecheckout
            setTimeout(function () {
                $.async(config.selectors, function () {
                    if (done) {
                        return;
                    }

                    done = true;

                    initGeolocation().then(function () {
                        // eslint-disable-next-line max-nested-callbacks
                        $.async(config.fields, function (el) {
                            self.initAutocomplete(el, {
                                types: $(el).is(config.streetSelectors) ? ['address'] : ['(regions)']
                            });
                        });
                    });
                });
            }, 100);
        },

        /**
         * Initialize address autocomplete on the element
         * @param {Element} el
         */
        initAutocomplete: function (el, settings) {
            var self = this,
                autocomplete;

            if (el.addressAutocomplete || authFailed) {
                return;
            }

            $(el).data('old-placeholder', $(el).attr('placeholder') || '');
            $(el).data('old-style', $(el).attr('style') || '');

            if (config.country.length) {
                settings.componentRestrictions = {
                    country: config.country
                };
            }

            autocomplete = new google.maps.places.Autocomplete(el, settings);
            autocomplete.addListener(
                'place_changed',
                placeChangedHandler.bind(self, el)
            );

            el.addressAutocomplete = autocomplete;

            initCountryRestrictions(el, config.countrySelectors);
        },

        /**
         * @param {Element} el
         */
        destroyAutocomplete: function (el) {
            if (!el.addressAutocomplete) {
                return;
            }

            google.maps.event.clearInstanceListeners(el.addressAutocomplete);
            google.maps.event.clearInstanceListeners(el);

            $(el).attr('placeholder', $(el).data('old-placeholder'));
            $(el).attr('style', $(el).data('old-style'));
            $(el).removeProp('disabled');
        }
    });
});
