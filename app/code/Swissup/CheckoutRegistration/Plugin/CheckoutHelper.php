<?php

namespace Swissup\CheckoutRegistration\Plugin;

class CheckoutHelper
{
    /**
     * @var \Swissup\CheckoutRegistration\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\CheckoutRegistration\Helper\Data $helper
     */
    public function __construct(
        \Swissup\CheckoutRegistration\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Checkout\Helper\Data $subject
     * @param boolean $result
     * @return boolean
     */
    public function afterIsAllowedGuestCheckout(
        \Magento\Checkout\Helper\Data $subject,
        $result
    ) {
        if (!$result) {
            return $this->helper->isRegistrationRequired() || $this->helper->isRegistrationAutomatic();
        }

        return $result;
    }
}
