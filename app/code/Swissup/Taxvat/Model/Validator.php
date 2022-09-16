<?php

namespace Swissup\Taxvat\Model;

use Magento\Framework\Exception\RemoteServiceUnavailableException;

class Validator
{
    /**
     * @var \Magento\Customer\Model\Vat
     */
    private $customerVat;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cache;

    /**
     * @param \Magento\Customer\Model\Vat $customerVat
     * @param \Magento\Framework\App\CacheInterface $cache
     */
    public function __construct(
        \Magento\Customer\Model\Vat $customerVat,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        $this->customerVat = $customerVat;
        $this->cache = $cache;
    }

    /**
     * Validate VAT number using VIES service
     *
     * @param  string $countryCode
     * @param  string $vatNumber
     * @return bool
     * @throws RemoteServiceUnavailableException
     */
    public function isValid($countryCode, $vatNumber)
    {
        if (!$countryCode || !$vatNumber) {
            return false;
        }

        $cacheKey = $this->getCacheKey($countryCode, $vatNumber);
        $result = $this->cache->load($cacheKey);

        if (!is_numeric($result)) {
            $result = $this->fetchIsValid($countryCode, $vatNumber);
            $this->cache->save((int) $result, $cacheKey, [], 60 * 10);
        }

        return (bool) $result;
    }

    /**
     * @param  string $countryCode
     * @param  string $vatNumber
     * @return bool
     * @throws RemoteServiceUnavailableException
     */
    private function fetchIsValid($countryCode, $vatNumber)
    {
        $result = $this->customerVat->checkVatNumber($countryCode, $vatNumber);

        if (!$result->getRequestSuccess()) {
            throw new RemoteServiceUnavailableException(
                __("VAT validation service is not available")
            );
        }

        return $result->getIsValid();
    }

    /**
     * @param string $countryCode
     * @param string $vatNumber
     * @return string
     */
    private function getCacheKey($countryCode, $vatNumber)
    {
        return 'swissup_taxvat_' . $countryCode . '_' . $vatNumber;
    }
}
