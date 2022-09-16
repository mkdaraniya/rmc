<?php

namespace Swissup\AddressAutocomplete\Model\Config\Source;

class UnitNumberPlacement implements \Magento\Framework\Data\OptionSourceInterface
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
            ['value' => 'street_number_start', 'label' => __('Before Street Number')],
            ['value' => 'street_number_end', 'label' => __('After Street Number')],
        ];

        $result = array_merge($result, $this->addressFields->toOptionArray());

        return $result;
    }
}
