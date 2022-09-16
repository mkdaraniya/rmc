<?php

namespace Swissup\CheckoutRegistration\Model\System\Config\Source;

class RegistrationMode implements \Magento\Framework\Data\OptionSourceInterface
{
    const DEFAULT = 'default';
    const GUEST_ONLY = 'guest_only';
    const OPTIONAL = 'optional';
    const REQUIRED = 'required';
    const AUTOMATIC = 'automatic';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::DEFAULT, 'label' => __('Use Magento Settings')],
            ['value' => self::GUEST_ONLY, 'label' => __('Guest Checkout Only')],
            ['value' => self::OPTIONAL, 'label' => __('Registration is Allowed')],
            ['value' => self::REQUIRED, 'label' => __('Registration is Required')],
            ['value' => self::AUTOMATIC, 'label' => __('Register All Users Automatically')],
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
