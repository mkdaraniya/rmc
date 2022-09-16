<?php

namespace Swissup\DeliveryDate\Model\Config\Source;

class FirstAvailableDay implements \Magento\Framework\Data\OptionSourceInterface
{
    const DAY_ORDER = 'order';
    const DAY_BUSINESS = 'business';
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::DAY_ORDER, 'label' => __('Order Date')],
            ['value' => self::DAY_BUSINESS, 'label' => __('First Business Day since Order Date')],
        ];
    }
}
