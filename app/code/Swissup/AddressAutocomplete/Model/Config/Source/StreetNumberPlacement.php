<?php

namespace Swissup\AddressAutocomplete\Model\Config\Source;

class StreetNumberPlacement implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var CustomAddressFields
     */
    private $addressFields;

    /**
     * @param CustomAddressFields $addressFields
     */
    public function __construct(
        CustomAddressFields $addressFields
    ) {
        $this->addressFields = $addressFields;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [
            ['value' => 'line1_start', 'label' => __('Address Line 1 Start')],
            ['value' => 'line1_end', 'label' => __('Address Line 1 End')],
            ['value' => 'line2', 'label' => __('Address Line 2')],
        ];

        $result = array_merge($result, $this->addressFields->toOptionArray());

        return $result;
    }
}
