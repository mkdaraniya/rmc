<?php

namespace Swissup\Firecheckout\Observer;

class RedirectToCheckout implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\Firecheckout\Helper\Data
     */
    protected $helper;

    /**
     */
    public function __construct(
        \Swissup\Firecheckout\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Set redirect to firecheckout after add to cart
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // if this solution works bad then implement plugin for
        // \Magento\Checkout\Controller\Cart::getBackUrl
        if ($this->helper->isFirecheckoutEnabled()
            && $this->helper->isRedirectToFirecheckoutEnabled()
        ) {
            $request = $observer->getRequest();
            if (!$request->getParam('return_url')) {
                $request->setParam(
                    'return_url',
                    $this->helper->getFirecheckoutUrl()
                );
            }
        }
    }
}
