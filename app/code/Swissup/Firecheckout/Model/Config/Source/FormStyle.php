<?php

namespace Swissup\Firecheckout\Model\Config\Source;

class FormStyle implements \Magento\Framework\Option\ArrayInterface
{
    const HORIZONTAL = 'horizontal';
    const BASIC      = 'basic';
    const COMPACT    = 'compact';
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::HORIZONTAL, 'label' => __('Horizontal (Label aside of the field)')],
            ['value' => self::BASIC,      'label' => __('Basic (Same as horizontal, except label above the field)')],
            ['value' => self::COMPACT,    'label' => __('Compact (Multiple fields per row)')],
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
