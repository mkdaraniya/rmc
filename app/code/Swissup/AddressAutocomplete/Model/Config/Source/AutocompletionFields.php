<?php

namespace Swissup\AddressAutocomplete\Model\Config\Source;

class AutocompletionFields implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'city', 'label' => __('City')],
            ['value' => 'street', 'label' => __('Street Address')],
            ['value' => 'postcode', 'label' => __('Postcode')],
        ];
    }
}
