<?php

namespace Swissup\Geoip\Model\Config\Source;

class MaxmindDatabaseEdition implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'GeoLite2-City', 'label' => __('GeoLite2-City (Free)')],
            ['value' => 'GeoIP2-City', 'label' => __('GeoIP2-City (Paid)')],
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
