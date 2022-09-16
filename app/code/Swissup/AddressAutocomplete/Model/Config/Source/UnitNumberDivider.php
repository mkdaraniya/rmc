<?php

namespace Swissup\AddressAutocomplete\Model\Config\Source;

class UnitNumberDivider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '{{, }}', 'label' => __('Comma: ", "')],
            ['value' => '{{ / }}', 'label' => __('Slash: " / "')],
        ];
    }
}
