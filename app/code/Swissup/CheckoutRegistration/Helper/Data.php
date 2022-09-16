<?php

namespace Swissup\CheckoutRegistration\Helper;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Swissup\CheckoutRegistration\Model\System\Config\Source\RegistrationMode;

class Data extends AbstractHelper
{
    /**
     * @var string
     */
    private $registrationMode;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $cartRepository
    ) {
        parent::__construct($context);

        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->getRegistrationMode() !== RegistrationMode::DEFAULT;
    }

    /**
     * @return boolean
     */
    public function isRegistrationAllowed()
    {
        $modes = [
            RegistrationMode::OPTIONAL,
            RegistrationMode::REQUIRED,
            RegistrationMode::AUTOMATIC,
        ];

        return in_array($this->getRegistrationMode(), $modes);
    }

    /**
     * @return boolean
     */
    public function isRegistrationRequired()
    {
        return $this->getRegistrationMode() === RegistrationMode::REQUIRED;
    }

    /**
     * @return boolean
     */
    public function isRegistrationAutomatic()
    {
        return $this->getRegistrationMode() === RegistrationMode::AUTOMATIC;
    }

    /**
     * @return boolean
     */
    public function isRegistrationPasswordAllowed()
    {
        $modesWithPassword = [
            RegistrationMode::OPTIONAL,
            RegistrationMode::REQUIRED,
        ];

        return in_array($this->getRegistrationMode(), $modesWithPassword);
    }

    /**
     * @return string
     */
    public function getRegistrationMode()
    {
        if ($this->registrationMode) {
            return $this->registrationMode;
        }

        $path = 'checkout_registration/general/mode';
        if ($this->hasDownloadableItemsInCart()) {
            $path = 'checkout_registration/general/mode_downloadable';
        }

        $mode = $this->getConfigValue($path);

        if (!$mode) {
            $mode = RegistrationMode::DEFAULT;
        }

        $this->registrationMode = $mode;

        return $mode;
    }

    /**
     * Get specific config value
     *
     * @param  string $path
     * @param  string $scope
     * @return string
     */
    public function getConfigValue($path, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($path, $scope);
    }

    /**
     * @return boolean
     */
    private function hasDownloadableItemsInCart()
    {
        $quoteId = $this->checkoutSession->getQuoteId();

        if (!$quoteId) {
            return false;
        }

        $quote = $this->cartRepository->get($quoteId);

        foreach ($quote->getAllItems() as $item) {
            $product = $item->getProduct();
            if ($product && $product->getTypeId() === Type::TYPE_DOWNLOADABLE) {
                return true;
            }
        }

        return false;
    }
}
