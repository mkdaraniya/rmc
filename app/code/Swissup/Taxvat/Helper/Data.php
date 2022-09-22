<?php
namespace Swissup\Taxvat\Helper;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Exception\RemoteServiceUnavailableException;

class Data extends AbstractHelper
{
    /**
     * Path to store config is VAT field enabled
     *
     * @var string
     */
    const VAT_ENABLED = 'customer/create_account/vat_frontend_visibility';
    /**
     * Path to store config is VIES validation enabled
     *
     * @var string
     */
    const VALIDATION_ENABLED = 'taxvat/general/validate';
    /**
     * Path to store config
     *
     * @var string
     */
    const ALLOW_INVALID = 'taxvat/general/allow_invalid';
    /**
     * Path to store config is VAT field required
     *
     * @var string
     */
    const VAT_FIELD_REQUIRED = 'taxvat/general/required';
    /**
     * @var string
     */
    const TAX_FREE_ENABLED = 'taxvat/general/tax_free';
    /**
     * @var string
     */
    const TAX_FREE_ALL = 'taxvat/general/tax_free_all';
    /**
     * @var string
     */
    const TAX_FREE_COUNTRIES = 'taxvat/general/tax_free_countries';

    /**
     * @var \Swissup\Taxvat\Model\Validator
     */
    private $validator;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Swissup\Taxvat\Model\Validator $validator
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Swissup\Taxvat\Model\Validator $validator
    ) {
        $this->validator = $validator;
        parent::__construct($context);
    }

    protected function _getConfig($key)
    {
        return $this->scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if VAT field is enabled in admin
     * @return boolean
     */
    public function isVatFieldEnabled()
    {
        return (bool)$this->_getConfig(self::VAT_ENABLED);
    }

    /**
     * Check if VAT validation is enabled in admin
     * @return boolean
     */
    public function isValidationEnabled()
    {
        return (bool)$this->_getConfig(self::VALIDATION_ENABLED);
    }

    /**
     * Check if VAT field is required
     * @return boolean
     */
    public function isVatRequired()
    {
        return (bool)$this->_getConfig(self::VAT_FIELD_REQUIRED);
    }

    /**
     * Check if both VAT field and validation are enabled
     * @return bool
     */
    public function canValidateVat()
    {
        return self::isVatFieldEnabled() && self::isValidationEnabled();
    }

    /**
     * Check if both VAT field and validation are enabled
     * @return bool
     */
    public function allowInvalidVat()
    {
        return (bool)$this->_getConfig(self::ALLOW_INVALID);
    }

    /**
     * Check if tax should be removed for valid VAT numbers
     *
     * @param string $countryCode
     * @return bool
     */
    public function isTaxFreeEnabled($countryCode = null)
    {
        $isEnabled = (bool)$this->_getConfig(self::TAX_FREE_ENABLED);
        if (!$countryCode || !$isEnabled) {
            return $isEnabled;
        }

        $isEnabledForCountry = (bool)$this->_getConfig(self::TAX_FREE_ALL);
        if (!$isEnabledForCountry) {
            $allowedCountries = $this->_getConfig(self::TAX_FREE_COUNTRIES);
            if ($allowedCountries) {
                $allowedCountries = explode(',', $allowedCountries);
                $isEnabledForCountry = in_array($countryCode, $allowedCountries);
            }
        }

        return $isEnabledForCountry;
    }

    /**
     * @param mixed $needles
     * @return boolean
     */
    public function hasInUrl($needles)
    {
        if (!is_array($needles)) {
            $needles = [$needles];
        }

        $pathInfo = $this->_request->getPathInfo();
        foreach ($needles as $needle) {
            if (strpos($pathInfo, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function isPost()
    {
        return $this->_request->isPost();
    }

    /**
     * @param  string $countryCode
     * @param  string $vatNumber
     * @return bool
     * @throws RemoteServiceUnavailableException
     */
    public function validateVat($countryCode, $vatNumber)
    {
        return $this->validator->isValid($countryCode, $vatNumber);
    }

    /**
     * @param \Magento\Framework\DataObject|\Magento\Customer\Model\Address|\Magento\Quote\Api\Data\AddressInterface $address
     * @param boolean $strict - This option is used for zero tax calculation.
     * @return boolean
     */
    public function validateAddress($address, $strict = false)
    {
        $allowEmpty = $strict ? false : !$this->isVatRequired();
        $allowInvalid = $strict ? false : $this->allowInvalidVat();

        $country = $address->getCountryId();
        $vat = $address->getVatId();

        if ($allowEmpty && (!$country || !$vat)) {
            return true;
        } elseif (!$allowEmpty && !$vat) {
            return false;
        }

        if ($allowInvalid) {
            return true;
        }

        try {
            $isValid = $this->validateVat($country, $vat);
        } catch (RemoteServiceUnavailableException $e) {
            $isValid = !$strict;
        }

        return $isValid;
    }
}
