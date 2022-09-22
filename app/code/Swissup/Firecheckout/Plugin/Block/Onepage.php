<?php

namespace Swissup\Firecheckout\Plugin\Block;

class Onepage
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Swissup\Firecheckout\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\JsonHexTag
     */
    private $serializer;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Swissup\Firecheckout\Helper\Data $helper
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Swissup\Firecheckout\Helper\Data $helper
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;

        // magento 2.3.1 and higher
        if (class_exists(\Magento\Framework\Serialize\Serializer\JsonHexTag::class)) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Serialize\Serializer\JsonHexTag::class);
        }
    }

    /**
     * @param \Magento\Checkout\Block\Onepage $subject
     * @param string $jsLayout
     * @return string
     */
    public function afterGetJsLayout(
        \Magento\Checkout\Block\Onepage $subject,
        $jsLayout
    ) {
        if (!$this->helper->isOnFirecheckoutPage() ||
            !$this->helper->isEmailOnSeparateStep() ||
            $this->customerSession->isLoggedIn() ||
            $this->checkoutSession->getQuote()->getIsVirtual()
        ) {
            return $jsLayout;
        }

        $jsLayout = $this->unserialize($jsLayout);

        $emailStep = [
            'component' => 'Swissup_Firecheckout/js/view/email-step',
            'sortOrder' => 0,
            'children' => [
                'customer-email' => [],
                // 'before-form' => [],
            ],
        ];

        foreach ($emailStep['children'] as $key => $values) {
            $config = $jsLayout['components']['checkout']
                ['children']['steps']
                ['children']['shipping-step']['children']['shippingAddress']
                ['children'][$key] ?? [];

            if ($config) {
                $emailStep['children'][$key] = array_replace($config, $values);
            } else {
                unset($emailStep['children'][$key]);
            }

            unset($jsLayout['components']['checkout']
                ['children']['steps']
                ['children']['shipping-step']['children']['shippingAddress']
                ['children'][$key]);
        }

        $jsLayout['components']['checkout']['children']['steps']['children']['email-step'] = $emailStep;

        // Magento_InventoryInStorePickup integration
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']
                ['store-pickup']['children']['store-selector']['children']['customer-email'])
        ) {
            $jsLayout['components']['checkout']['children']['steps']['children']
                ['store-pickup']['children']['store-selector']['children']
                ['customer-email']['config']['links']['email'] =
                    'checkout.steps.email-step.customer-email:email';
        }

        return $this->serialize($jsLayout);
    }

    /**
     * @param array $data
     * @return string
     */
    private function serialize($data)
    {
        if ($this->serializer) {
            return $this->serializer->serialize($data);
        }
        return json_encode($data, JSON_HEX_TAG);
    }

    /**
     * @param string $string
     * @return array
     */
    private function unserialize($string)
    {
        if ($this->serializer) {
            return $this->serializer->unserialize($string);
        }
        return json_decode($string, true);
    }
}
