<?php

namespace Swissup\Firecheckout\Plugin\Helper;

class CheckoutData
{
    /**
     * @var \Swissup\Firecheckout\Helper\Data
     */
    protected $helper;

    /**
     * @var \Swissup\Firecheckout\Helper\Config\Payment
     */
    protected $paymentConfig;

    /**
     * @param \Swissup\Firecheckout\Helper\Data $helper
     * @param \Swissup\Firecheckout\Helper\Config\Payment $paymentConfig
     */
    public function __construct(
        \Swissup\Firecheckout\Helper\Data $helper,
        \Swissup\Firecheckout\Helper\Config\Payment $paymentConfig
    ) {
        $this->helper = $helper;
        $this->paymentConfig = $paymentConfig;
    }

    /**
     * Overriden to return true, when onepage checkout is disabled.
     *
     * This check is used to render header cart actions and subtotal
     *
     * @param \Magento\Checkout\Helper\Data $subject
     * @param boolean $result
     * @return boolean
     */
    public function afterCanOnepageCheckout(
        \Magento\Checkout\Helper\Data $subject,
        $result
    ) {
        if ($this->helper->isFirecheckoutEnabled()) {
            return true;
        }
        return $result;
    }

    /**
     * Programmatically move address to payment page if firecheckout config
     * wants to show billing address in custom place.
     *
     * @param mixed $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterIsDisplayBillingOnPaymentMethodAvailable(
        $subject,
        $result
    ) {
        if (!$this->helper->isOnFirecheckoutPage()) {
            // address is already on the payment page
            return $result;
        }

        $value = $this->paymentConfig->getDisplayBillingAddressOn();
        $value = str_replace('default-', '', $value); // firecheckout supports default values as well

        return (bool) !$value;
    }
}
