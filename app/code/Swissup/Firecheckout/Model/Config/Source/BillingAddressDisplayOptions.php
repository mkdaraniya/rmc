<?php

namespace Swissup\Firecheckout\Model\Config\Source;

class BillingAddressDisplayOptions extends \Magento\Checkout\Model\Adminhtml\BillingAddressDisplayOptions
{
    const OPTION_MAGENTO_CONFIG = 'default';
    const OPTION_BEFORE_PAYMENT = 'before-payment-methods';
    const OPTION_BEFORE_SHIPPING = 'before-shipping-address';
    const OPTION_AFTER_SHIPPING = 'after-shipping-address';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = parent::toOptionArray();

        // fix double "selected" options
        foreach ($result as $i => $option) {
            $result[$i]['value'] = 'default-' . $option['value'];
        }

        array_unshift($result, [
            'label' => __('Use Magento Config (Sales > Checkout > Checkout Options)'),
            'value' => self::OPTION_MAGENTO_CONFIG
        ]);
        $result[] = [
            'label' => __('Payment Page (Above Payment Methods)'),
            'value' => self::OPTION_BEFORE_PAYMENT
        ];
        $result[] = [
            'label' => __('Shipping Page (Above Shipping Address)'),
            'value' => self::OPTION_BEFORE_SHIPPING
        ];
        $result[] = [
            'label' => __('Shipping Page (Below Shipping Address)'),
            'value' => self::OPTION_AFTER_SHIPPING
        ];

        return $result;
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
