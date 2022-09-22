<?php

namespace Swissup\Taxvat\Plugin\Model;

class CustomerVat
{
    private static $memo = [];

    /**
     * Prevent multiple requests to the VIES provider when
     * "Automatic assignment to Group" is enabled
     *
     * @param mixed $subject
     * @param callable $proceed
     * @param string $countryCode
     * @param string $vatNumber
     * @param string $requesterCountryCode
     * @param string $requesterVatNumber
     * @return \Magento\Framework\DataObject
     */
    public function aroundCheckVatNumber(
        $subject,
        callable $proceed,
        $countryCode,
        $vatNumber,
        $requesterCountryCode = '',
        $requesterVatNumber = ''
    ) {
        $key = implode('_', [
            $countryCode,
            $vatNumber,
            $requesterCountryCode,
            $requesterVatNumber,
        ]);

        if (!isset(self::$memo[$key])) {
            self::$memo[$key] = $proceed(
                $countryCode,
                $vatNumber,
                $requesterCountryCode,
                $requesterVatNumber
            );
        }

        return self::$memo[$key];
    }
}
