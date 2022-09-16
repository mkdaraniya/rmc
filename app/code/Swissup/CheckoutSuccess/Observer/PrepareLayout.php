<?php

namespace Swissup\CheckoutSuccess\Observer;

class PrepareLayout implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\CheckoutSuccess\Helper\Data
     */
    private $helper;

    /**
     */
    public function __construct(
        \Swissup\CheckoutSuccess\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Add success page layout update to module config
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->getConfigValue('general/enabled')
            && $this->helper->isOnSuccessPage()
        ) {
            $observer->getLayout()->getUpdate()->addHandle('swissup_checkoutsuccess');

            if ($this->helper->getRequest()->getParam('builder')) {
                $observer->getLayout()->getUpdate()->addHandle('swissup_checkoutsuccess_builder');

                if ($this->helper->getRequest()->getParam('block')) {
                    $observer->getLayout()->getUpdate()->addHandle('swissup_checkoutsuccess_builder_getBlock');
                }
            }
        }
    }
}
