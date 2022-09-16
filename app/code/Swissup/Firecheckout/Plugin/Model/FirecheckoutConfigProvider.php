<?php

namespace Swissup\Firecheckout\Plugin\Model;

class FirecheckoutConfigProvider
{
    /**
     * @var \Swissup\Firecheckout\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @param \Swissup\Firecheckout\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Firecheckout\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->paymentMethodManagement = $paymentMethodManagement;
    }

    /**
     * Redefine checkoutUrl parameter, and load paymentMethods if they are empty
     *
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array $result
     * @return string
     */
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        array $result
    ) {
        if ($this->helper->isOnFirecheckoutPage()) {
            $this->fixMissingDefaultAddressId($result);

            if (!$this->helper->isMultistepLayout() && empty($result['paymentMethods'])) {
                $quote = $this->checkoutSession->getQuote();
                if (!$quote->getIsVirtual()) {
                    foreach ($this->paymentMethodManagement->getList($quote->getId()) as $paymentMethod) {
                        $result['paymentMethods'][] = [
                            'code' => $paymentMethod->getCode(),
                            'title' => $paymentMethod->getTitle()
                        ];
                    }
                }
            }
            $result['checkoutUrl'] = $this->helper->getFirecheckoutUrl();
        }
        return $result;
    }

    /**
     * Fixed missing default_shipping value when expanded layout is used
     * and client somehow removed its default address.
     *
     * This is done to prevent js errors caused by code
     * in Magento_Checkout/js/view/billing-address.js:
     *
     *  ```
     *  quote.shippingAddress().getCacheKey()
     *  ```
     *
     * @param array &$result
     * @return void
     */
    private function fixMissingDefaultAddressId(&$result)
    {
        if (!$this->helper->isMultistepLayout() &&
            !empty($result['customerData']) &&
            !empty($result['customerData']['addresses']) &&
            empty($result['customerData']['default_shipping'])
        ) {
            $id = key($result['customerData']['addresses']);
            $result['customerData']['default_shipping'] = $id;
            $result['customerData']['addresses'][$id]['default_shipping'] = true;
        }
    }

    /**
     * Retrieve checkout URL
     *
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param string $result
     * @return string
     */
    public function afterGetCheckoutUrl(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        $result
    ) {
        if (!$this->helper->isFirecheckoutEnabled()) {
            return $result;
        }

        if ($this->helper->isOnStandardCheckoutPage()) {
            return $result . '?onepage=1';
        }

        return $this->helper->getFirecheckoutUrl();
    }
}
