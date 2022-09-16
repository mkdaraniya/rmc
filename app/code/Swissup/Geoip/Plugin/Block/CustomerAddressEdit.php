<?php

namespace Swissup\Geoip\Plugin\Block;

use Magento\Customer\Api\Data\AddressInterface;

class CustomerAddressEdit
{
    /**
     * @var \Swissup\Geoip\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    public function __construct(
        \Swissup\Geoip\Helper\Data $helper,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->helper = $helper;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param \Magento\Customer\Block\Address\Edit $subject
     */
    public function beforeToHtml(\Magento\Customer\Block\Address\Edit $subject)
    {
        if (!$this->helper->isEnabled()) {
            return;
        }

        $address = $subject->getAddress();
        if (!$address instanceof AddressInterface || $address->getId()) {
            return;
        }

        $record = $this->helper->detect();
        if (!$record->isValid()) {
            return;
        }

        $data = [
            'city' => $address->getCity(),
            'region' => $address->getRegion(),
            'region_id' => $address->getRegionId(),
            'postcode' => $address->getPostcode(),
            'country_id' => $address->getCountryId()
        ];
        $data = array_filter($data);
        $data = array_merge($record->toAddressArray(), $data);

        $this->dataObjectHelper->populateWithArray(
            $address,
            $data,
            AddressInterface::class
        );
    }
}
