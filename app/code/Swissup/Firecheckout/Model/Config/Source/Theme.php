<?php

namespace Swissup\Firecheckout\Model\Config\Source;

class Theme implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'default', 'label' => __('Default')],
            ['value' => 'light', 'label' => __('Light')],
            ['value' => 'midnight', 'label' => __('Midnight')],
            ['value' => 'round', 'label' => __('Round')],
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
