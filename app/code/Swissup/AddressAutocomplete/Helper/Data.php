<?php

namespace Swissup\AddressAutocomplete\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const CONFIG_IS_ENABLED = 'address_autocomplete/main/enable';
    const CONFIG_API_KEY = 'address_autocomplete/main/api_key';
    const CONFIG_FIELDS = 'address_autocomplete/main/fields';
    const CONFIG_GEOLOCATION_IS_ENABLED = 'address_autocomplete/main/geolocation';
    const CONFIG_RESTRICT_TO_CURRENT = 'address_autocomplete/main/restrict_to_current';
    const CONFIG_RESTRICTED_COUNTRIES = 'address_autocomplete/main/country';
    const CONFIG_STREET_PLACEMENT = 'address_autocomplete/main/street_number_placement';
    const CONFIG_UNIT_NUMBER = 'address_autocomplete/main/unit_number';
    const CONFIG_UNIT_NUMBER_PLACEMENT = 'address_autocomplete/main/unit_number_placement';
    const CONFIG_UNIT_NUMBER_DIVIDER = 'address_autocomplete/main/unit_number_divider';
    const CONFIG_USE_ADDRESS_MAPPING = 'address_autocomplete/main/use_address_mapping';
    const CONFIG_ADDRESS_MAPPING = 'address_autocomplete/main/address_mapping';

    /**
     *
     * @var \Magento\Framework\Locale\Resolver
     */
    private $localeResolver;

    /**
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
    ) {
        $this->localeResolver = $localeResolver;
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context);
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return (bool) $this->getConfigValue(self::CONFIG_IS_ENABLED);
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->getConfigValue(self::CONFIG_API_KEY);
    }

    /**
     * @return string
     */
    public function getStreetNumberPlacement()
    {
        return $this->getConfigValue(self::CONFIG_STREET_PLACEMENT);
    }

    /**
     * @return array
     */
    public function getUnitNumberConfig()
    {
        if (!$this->getConfigValue(self::CONFIG_UNIT_NUMBER)) {
            return [];
        }

        return [
            'placement' => $this->getConfigValue(self::CONFIG_UNIT_NUMBER_PLACEMENT),
            'divider' => $this->getConfigValue(self::CONFIG_UNIT_NUMBER_DIVIDER),
        ];
    }

    /**
     * @return array
     */
    public function getAdvancedAddressMapping()
    {
        if (!$this->getConfigValue(self::CONFIG_USE_ADDRESS_MAPPING)) {
            return [];
        }

        $mapping = $this->getConfigValue(self::CONFIG_ADDRESS_MAPPING);

        if (!$mapping) {
            return [];
        }

        try {
            return $this->jsonSerializer->unserialize($mapping);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @return boolean
     */
    public function getRestrictToCurrentCountry()
    {
        return (bool) $this->getConfigValue(self::CONFIG_RESTRICT_TO_CURRENT);
    }

    /**
     * @return array
     */
    public function getRestrictedCountries()
    {
        if ($this->getRestrictToCurrentCountry()) {
            return [];
        }

        $country = $this->getConfigValue(self::CONFIG_RESTRICTED_COUNTRIES);

        return $country ? array_filter(explode(',', strtolower($country))) : [];
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return str_replace('_', '-', $this->localeResolver->getLocale());
    }

    /**
     * @return string
     */
    public function getStreetSelector()
    {
        return 'input[name="street[0]"],#street_1';
    }

    /**
     * @return string
     */
    public function getPostcodeSelector()
    {
        return 'input[name="postcode"],#zip';
    }

    /**
     * @return string
     */
    public function getCitySelector()
    {
        return 'input[name="city"],#city';
    }

    /**
     * @return string
     */
    public function getFieldsSelector()
    {
        $config = (string)$this->getConfigValue(self::CONFIG_FIELDS);
        $config = array_flip(explode(',', $config));
        $fieldsSelectors= [];

        if (!$config || array_key_exists('street', $config)) {
            $fieldsSelectors[] = $this->getStreetSelector();
        }

        if ($config && array_key_exists('postcode', $config)) {
            $fieldsSelectors[] = $this->getPostcodeSelector();
        }

        if ($config && array_key_exists('city', $config)) {
            $fieldsSelectors[] = $this->getCitySelector();
        }

        return implode(',', $fieldsSelectors);
    }

    /**
     * @return boolean
     */
    public function isGeolocationEnabled()
    {
        return (bool) $this->getConfigValue(self::CONFIG_GEOLOCATION_IS_ENABLED);
    }

    /**
     * @return array
     */
    public function getComponentConfig()
    {
        return [
            'componentDisabled' => !$this->isEnabled(),
            'settings' => [
                'apiKey' => $this->getApiKey(),
                'locale' => $this->getLocale(),
                'streetNumberPlacement' => $this->getStreetNumberPlacement(),
                'unitNumber' => $this->getUnitNumberConfig(),
                'restrictToCurrentCountry' => $this->getRestrictToCurrentCountry(),
                'country' => $this->getRestrictedCountries(),
                'mapping' => $this->getAdvancedAddressMapping(),
                'fields' => $this->getFieldsSelector(),
                'geolocation' => $this->isGeolocationEnabled(),
                'selectors' => $this->getStreetSelector(),
                'citySelectors' => $this->getCitySelector(),
                'streetSelectors' => $this->getStreetSelector(),
                'postcodeSelectors' => $this->getPostcodeSelector(),
                'countrySelectors' => 'select[name="country_id"]',
            ],
        ];
    }

    /**
     * @param string $path
     * @param string $scope
     * @return string
     */
    protected function getConfigValue($path, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($path, $scope);
    }
}
