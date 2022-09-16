<?php

namespace Swissup\Firecheckout\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Swissup\Firecheckout\Helper\Data
     */
    protected $helper;

    /**
     * @var \Swissup\Firecheckout\Helper\Config\Payment
     */
    protected $paymentHelper;

    /**
     * @var \Swissup\Firecheckout\Helper\Config\Shipping
     */
    protected $shippingHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param \Swissup\Firecheckout\Helper\Data $helper
     * @param \Swissup\Firecheckout\Helper\Config\Payment $paymentHelper
     * @param \Swissup\Firecheckout\Helper\Config\Shipping $shippingHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Swissup\Firecheckout\Helper\Data $helper,
        \Swissup\Firecheckout\Helper\Config\Payment $paymentHelper,
        \Swissup\Firecheckout\Helper\Config\Shipping $shippingHelper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->paymentHelper = $paymentHelper;
        $this->shippingHelper = $shippingHelper;
        $this->checkoutSession = $checkoutSession;
    }

    public function getConfig()
    {
        if (!$this->helper->isOnFirecheckoutPage()) {
            return [];
        }

        return [
            'isFirecheckout' => true,
            'swissup' => [
                'firecheckout' => [
                    'layout' => $this->helper->getFirecheckoutLayout(),
                    'emailOnSeparateStep' => $this->helper->isEmailOnSeparateStep(),
                    'shipping' => [
                        'default_method' => $this->shippingHelper->getDefaultShippingMethod()
                    ],
                    'payment' => [
                        'default_method' => $this->paymentHelper->getDefaultPaymentMethod(),
                    ],
                    'dependencies' => [
                        'payment' => $this->getPaymentDependencies()
                    ]
                ]
            ]
        ];
    }

    protected function getPaymentDependencies()
    {
        $result = [];

        if (!$this->checkoutSession->getQuote()->getIsVirtual()) {
            $config = $this->scopeConfig->getValue(
                'payment',
                ScopeInterface::SCOPE_WEBSITE
            );
            foreach ($config as $code => $paymentConfig) {
                if (!empty($paymentConfig['active']) &&
                    !empty($paymentConfig['allowspecific'])
                ) {
                    $result[] = 'address';
                    break;
                }
            }
        }

        return $result;
    }
}
