<?php

namespace Swissup\Firecheckout\Model\Config\Source;

class PlaceOrderPosition implements \Magento\Framework\Data\OptionSourceInterface
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
            ['value' => self::VALUE_EMTPY, 'label' => __('Use Default Config')],
            ['value' => 'below-payment-section', 'label' => __('Below Payment Section')]
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
