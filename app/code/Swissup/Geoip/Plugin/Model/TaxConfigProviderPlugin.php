<?php

namespace Swissup\Geoip\Plugin\Model;

class TaxConfigProviderPlugin
{
    /**
     *
     * @var \Swissup\Geoip\Helper\Data
     */
    protected $helper = [];

    /**
     * @param \Swissup\Geoip\Helper\Data helper
     */
    public function __construct(\Swissup\Geoip\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param  \Magento\Tax\Model\TaxConfigProvider $subject
     * @param  array $result
     * @return array
     */
    public function afterGetConfig(
        \Magento\Tax\Model\TaxConfigProvider $subject,
        $result
    ) {
        if (!$this->helper->isEnabled()) {
            return $result;
        }

        $record = $this->helper->detect();
        if (!$record->isValid()) {
            return $result;
        }

        $result['swissup']['geoip']['enabled'] = true;
        $result['defaultCity'] = $record->getCityName();
        $result['defaultCountryId'] = $record->getCountryCode();
        $result['defaultRegionId'] = $record->getRegion()->getId();
        $result['defaultPostcode'] = $record->getPostalCode();

        return $result;
    }
}
