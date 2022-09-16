<?php

namespace Swissup\Firecheckout\Model\Config\Source;

class ShippingMethods extends \Magento\Shipping\Model\Config\Source\Allmethods
{
    /**
     * @return array
     */
    public function toOptionArray($isActiveOnlyFlag = false)
    {
        $result = parent::toOptionArray($isActiveOnlyFlag);
        $emptyItem = ['value' => '', 'label' => __('None')];

        if (!isset($result[0]) || !empty($result[0]['value'])) {
            array_unshift($result, $emptyItem);
        } elseif (empty($result[0]['label'])) {
            $result[0]['label'] = $emptyItem['label'];
        }

        return $result;
    }
}
