<?php

namespace Swissup\Taxvat\Plugin\Model;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\RemoteServiceUnavailableException;

class TaxCalculation
{
    /**
     * @var \Swissup\Taxvat\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\Taxvat\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Taxvat\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CustomerAccountManagement $customerAccountManagement
    ) {
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->customerAccountManagement = $customerAccountManagement;
    }

    /**
     * Checks if plugin should work or not.
     *
     * Disable in catalog intentionally because of cached prices
     * in blocks cache (Highlight and Catalog widgets).
     *
     * @param \Magento\Framework\DataObject $request
     * @return boolean
     */
    private function isEnabled($request = null)
    {
        $countryId = $request ? $request->getCountryId() : null;

        if (!$this->helper->canValidateVat() ||
            !$this->helper->isTaxFreeEnabled($countryId)
        ) {
            return false;
        }

        $isCheckout = $this->helper->hasInUrl([
            '/checkout/',
            '/firecheckout/',
            '/rest/',
            '/paypal/',
        ]);

        return $isCheckout || $this->helper->isPost();
    }

    /**
     * @param \Magento\Tax\Model\Calculation $subject
     * @param callable $proceed
     * @return \Magento\Framework\DataObject
     */
    public function aroundGetRateRequest(
        \Magento\Tax\Model\Calculation $subject,
        callable $proceed,
        $shippingAddress = null,
        $billingAddress = null,
        $customerTaxClass = null,
        $store = null,
        $customerId = null
    ) {
        $request = $proceed(
            $shippingAddress,
            $billingAddress,
            $customerTaxClass,
            $store,
            $customerId
        );

        if ($this->isEnabled() && !$request->hasVatId()) {
            $address = $this->getAddressForTax($shippingAddress, $billingAddress, $store, $customerId);
            if ($address) {
                $request->setVatId($address->getVatId());
            }
        }

        return $request;
    }

    private function getAddressForTax($shippingAddress, $billingAddress, $store, $customerId)
    {
        $basedOn = $this->scopeConfig->getValue(
            \Magento\Tax\Model\Config::CONFIG_XML_PATH_BASED_ON,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

        if ($basedOn !== 'shipping' && $basedOn !== 'billing') {
            return false;
        }

        if ($shippingAddress === false && $basedOn == 'shipping' ||
            $billingAddress === false && $basedOn == 'billing'
        ) {
            return false;
        }

        if ($basedOn == 'shipping') {
            $address = $shippingAddress;
        } else {
            $address = $billingAddress;
        }

        if ($address === null || !$address->getCountryId()) {
            if ($customerId) {
                try {
                    $defaultBilling = $this->customerAccountManagement->getDefaultBillingAddress($customerId);
                } catch (NoSuchEntityException $e) {
                }

                try {
                    $defaultShipping = $this->customerAccountManagement->getDefaultShippingAddress($customerId);
                } catch (NoSuchEntityException $e) {
                }

                if ($basedOn == 'billing' && isset($defaultBilling) && $defaultBilling->getCountryId()) {
                    $address = $defaultBilling;
                } elseif ($basedOn == 'shipping' && isset($defaultShipping) && $defaultShipping->getCountryId()) {
                    $address = $defaultShipping;
                }
            } else {
                if ($basedOn == 'billing' && $shippingAddress && $shippingAddress->getCountryId()) {
                    $address = $shippingAddress;
                } elseif ($basedOn == 'shipping' && $billingAddress && $billingAddress->getCountryId()) {
                    $address = $billingAddress;
                }
            }
        }

        return $address;
    }

    /**
     * @param \Magento\Tax\Model\Calculation $subject
     * @param callable $proceed
     * @param \Magento\Framework\DataObject $request
     * @return float
     */
    public function aroundGetRate(
        \Magento\Tax\Model\Calculation $subject,
        callable $proceed,
        $request
    ) {
        if (!$this->isEnabled($request)) {
            return $proceed($request);
        }

        if ($this->helper->validateAddress($request, true)) {
            return 0;
        }

        return $proceed($request);
    }

    /**
     * @param \Magento\Tax\Model\Calculation $subject
     * @param callable $proceed
     * @param \Magento\Framework\DataObject $request
     * @return array
     */
    public function aroundGetAppliedRates(
        \Magento\Tax\Model\Calculation $subject,
        callable $proceed,
        $request
    ) {
        if (!$this->isEnabled($request)) {
            return $proceed($request);
        }

        if ($this->helper->validateAddress($request, true)) {
            return [];
        }

        return $proceed($request);
    }
}
