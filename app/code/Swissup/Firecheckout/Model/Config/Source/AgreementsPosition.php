<?php

namespace Swissup\Firecheckout\Model\Config\Source;

class AgreementsPosition implements \Magento\Framework\Data\OptionSourceInterface
{
    const VALUE_EMTPY = '';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::VALUE_EMTPY,   'label' => __('Use Magento Config')],
            ['value' => 'above-place-order', 'label' => __('Above Place Order Button')],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->toOptionArray() as $item) {
            $result[$item['value']] = $item['label'];
        }
        return $result;
    }
}
