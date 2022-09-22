<?php

namespace Swissup\Firecheckout\Model\Config\Source;

class PageLayout implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'default', 'label' => __('Default Checkout Layout')],
            ['value' => 'empty',   'label' => __('Empty (No Header and Footer)')],
            ['value' => 'minimal', 'label' => __('Minimal (Header with store Logo only)')],
            ['value' => 'full',    'label' => __('Full')],
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
