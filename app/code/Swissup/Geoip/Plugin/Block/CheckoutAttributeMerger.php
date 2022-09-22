<?php

namespace Swissup\Geoip\Plugin\Block;

class CheckoutAttributeMerger
{
    /**
     * @var \Swissup\Geoip\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\Geoip\Helper\Data $helper
     */
    public function __construct(\Swissup\Geoip\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Magento\Checkout\Block\Checkout\AttributeMerger $subject
     * @param array $elements
     * @param string $providerName
     * @param string $dataScopePrefix
     * @param array $fields
     * @return array|null
     */
    public function beforeMerge(
        \Magento\Checkout\Block\Checkout\AttributeMerger $subject,
        $elements,
        $providerName,
        $dataScopePrefix,
        array $fields = []
    ) {
        if (!$this->helper->isEnabled()) {
            return null;
        }

        $record = $this->helper->detect();
        if (!$record->isValid()) {
            return null;
        }

        $data = $record->toAddressArray();
        $keys = [
            'city',
            'region_id',
            'country_id',
            'postcode',
        ];

        foreach ($keys as $key) {
            if (!isset($elements[$key]) || empty($data[$key])) {
                continue;
            }
            $elements[$key]['default'] = $data[$key];
        }

        return [
            $elements,
            $providerName,
            $dataScopePrefix,
            $fields
        ];
    }
}
