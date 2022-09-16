<?php

namespace Swissup\CheckoutRegistration\Helper;

use Magento\Customer\Model\AccountManagement;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Api\CartRepositoryInterface;
use Swissup\CheckoutRegistration\Model\System\Config\Source\RegistrationMode;

class RegistrationForm extends Data
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $cartRepository,
        CustomerSession $customerSession
    ) {
        parent::__construct($context, $checkoutSession, $cartRepository);

        $this->customerSession = $customerSession;
    }

    /**
     * Get config to use for component rendering
     *
     * @return array
     */
    public function getComponentConfig()
    {
        return [
            'checkbox' => [
                'checked' => $this->isCheckboxChecked(),
                'visible' => $this->isCheckboxVisible(),
            ],
            'pass' => [
                'minLength' => $this->getConfigValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH),
                'minCharacterSets' => $this->getConfigValue(AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER),
            ],
            'componentDisabled' => $this->isComponentDisabled(),
        ];
    }

    /**
     * Get config to use for component rendering inside shipping address section
     *
     * @return array
     */
    public function getComponentConfigForStandardQuote()
    {
        $config = $this->getComponentConfig();

        if ($this->isVirtualQuote()) {
            $config['componentDisabled'] = true;
        }

        return $config;
    }

    /**
     * Get config to use for component rendering inside shipping address section
     *
     * @return array
     */
    public function getComponentConfigForVirtualQuote()
    {
        $config = $this->getComponentConfig();

        if (!$this->isVirtualQuote()) {
            $config['componentDisabled'] = true;
        }

        return $config;
    }

    /**
     * @return boolean
     */
    public function isCheckboxChecked()
    {
        return $this->getRegistrationMode() === RegistrationMode::OPTIONAL && $this->getCheckboxStatus();
    }

    /**
     * @return boolean
     */
    public function isCheckboxVisible()
    {
        return $this->getRegistrationMode() !== RegistrationMode::REQUIRED;
    }

    /**
     * @return boolean
     */
    public function isComponentDisabled()
    {
        if ($this->isCustomerLoggedIn()) {
            return true;
        }

        $noFormModes = [
            RegistrationMode::DEFAULT,
            RegistrationMode::GUEST_ONLY,
            RegistrationMode::AUTOMATIC,
        ];

        return in_array($this->getRegistrationMode(), $noFormModes);
    }

    /**
     * @return boolean
     */
    public function getCheckboxStatus()
    {
        return $this->getConfigValue('checkout_registration/general/checkbox_status');
    }

    /**
     * @return boolean
     */
    private function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return bool
     */
    private function isVirtualQuote()
    {
        return $this->checkoutSession->getQuote()->isVirtual();
    }
}
