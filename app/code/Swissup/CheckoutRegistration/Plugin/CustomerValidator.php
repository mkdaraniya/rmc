<?php

namespace Swissup\CheckoutRegistration\Plugin;

class CustomerValidator
{
    /**
     * Fix for Magento 2.3.0 and 2.2.8
     *
     * @param \Magento\Framework\Validator $subject
     * @param boolean $result
     * @param mixed $value
     * @return boolean
     */
    public function afterIsValid(\Magento\Framework\Validator $subject, $result, $value = null)
    {
        if (!$result &&
            $value instanceof \Magento\Customer\Model\Customer &&
            $value->getData('ignore_validation_flag')
        ) {
            return true;
        }

        return $result;
    }
}
