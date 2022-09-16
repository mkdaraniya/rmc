<?php

namespace Swissup\Firecheckout\Model\Config\Source;

class BillingAddressSaveModes implements \Magento\Framework\Data\OptionSourceInterface
{
    const MODE_DEFAULT = 'default';
    const MODE_INSTANT = 'instant';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::MODE_DEFAULT, 'label' => __('Default ("Update" button is used to save billing address)')],
            ['value' => self::MODE_INSTANT, 'label' => __('Instant (Address is saved as soon as all required fields are filled)')],
        ];
    }
}
