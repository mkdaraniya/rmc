<?php

namespace Swissup\Firecheckout\Model\Config\Source;

class PaymentMethods extends \Magento\Payment\Model\Config\Source\Allmethods
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = parent::toOptionArray();
        $emptyItem = ['value' => '', 'label' => __('None')];

        if (!isset($result[0]) || !empty($result[0]['value'])) {
            array_unshift($result, $emptyItem);
        } elseif (empty($result[0]['label'])) {
            $result[0]['label'] = $emptyItem['label'];
        }

        return $result;
    }
}
