<?php

namespace Swissup\AddressFieldManager\Model\Address\Validator;

use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Address\ValidatorInterface;
use Magento\Store\Model\ScopeInterface;

class Country implements ValidatorInterface
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryData;

    /**
     * @var \Magento\Directory\Model\AllowedCountries
     */
    private $allowedCountriesReader;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Directory\Model\AllowedCountries $allowedCountriesReader
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Directory\Model\AllowedCountries $allowedCountriesReader,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->directoryData = $directoryData;
        $this->allowedCountriesReader = $allowedCountriesReader;
        $this->escaper = $escaper;
    }

    public function validate(AbstractAddress $address)
    {
        $errors = $this->validateCountry($address);
        if (empty($errors)) {
            $errors = $this->validateRegion($address);
        }

        return $errors;
    }

    /**
     * Validate country existence.
     *
     * @param AbstractAddress $address
     * @return array
     */
    private function validateCountry(AbstractAddress $address)
    {
        $errors = [];
        $countryId = $address->getCountryId();

        if (\Zend_Validate::is($countryId, 'NotEmpty') &&
            !in_array($countryId, $this->getWebsiteAllowedCountries($address), true)
        ) {
            $errors[] = __(
                'Invalid value of "%value" provided for the %fieldName field.',
                ['fieldName' => 'countryId', 'value' => $this->escaper->escapeHtml($countryId)]
            );
        }

        return $errors;
    }

    /**
     * Validate region existence.
     *
     * @param AbstractAddress $address
     * @return array
     * @throws \Zend_Validate_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function validateRegion(AbstractAddress $address)
    {
        $errors = [];
        $countryId = $address->getCountryId();
        $regionCollection = $address->getCountryModel()->getRegionCollection();
        $region = $address->getRegion();
        $regionId = (string) $address->getRegionId();
        $allowedRegions = $regionCollection->getAllIds();
        $isRegionRequired = $this->directoryData->isRegionRequired($countryId);

        if ($isRegionRequired && empty($allowedRegions) && !\Zend_Validate::is($region, 'NotEmpty')) {
            //If region is required for country and country doesn't provide regions list
            //region must be provided.
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'region']);
        } elseif ($isRegionRequired && $allowedRegions && !\Zend_Validate::is($regionId, 'NotEmpty')) {
            //If country has regions to select - it must be selected.
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'regionId']);
        } elseif ($allowedRegions && $regionId && !in_array($regionId, $allowedRegions, true)) {
            //If a region is selected then checking if it exists.
            $errors[] = __(
                'Invalid value of "%value" provided for the %fieldName field.',
                ['fieldName' => 'regionId', 'value' => $this->escaper->escapeHtml($regionId)]
            );
        }

        return $errors;
    }

    /**
     * Return allowed counties per website.
     *
     * @param AbstractAddress $address
     * @return array
     */
    private function getWebsiteAllowedCountries(AbstractAddress $address)
    {
        return $this->allowedCountriesReader->getAllowedCountries(
            ScopeInterface::SCOPE_STORE,
            $address->getData('store_id')
        );
    }
}
