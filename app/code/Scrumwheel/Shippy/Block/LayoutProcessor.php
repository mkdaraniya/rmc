<?php

namespace Scrumwheel\Shippy\Block;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    public function process($jsLayout)
    {
        $attributeCode = 'incoterm';
        $fieldConfiguration = [
            'component' => 'Magento_Ui/js/form/element/text',
            'config' => [
                'customScope' => 'shippingAddress.extension_attributes',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/text',
                // 'tooltip' => [
                //     'description' => 'Here you can leave delivery notes',
                // ],
            ],
            'dataScope' => 'shippingAddress.extension_attributes' . '.' . $attributeCode,
            'label' => 'Incoterm',
            'provider' => 'checkoutProvider',
            'sortOrder' => 1000,
            'validation' => [
                'required-entry' => true
            ],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
            'value' => ''
        ];

        $jsLayout['components']['checkout']['children']
        ['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']
        ['children'][$attributeCode] = $fieldConfiguration;
        return $jsLayout;
    }
}
