<?php

namespace Swissup\CheckoutRegistration\Observer;

class AddPageClassName implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfig;

    /**
     * @var \Swissup\Firecheckout\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \Swissup\CheckoutRegistration\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\View\Page\Config $pageConfig,
        \Swissup\CheckoutRegistration\Helper\Data $helper
    ) {
        $this->pageConfig = $pageConfig;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isEnabled()) {
            return;
        }

        $this->pageConfig->addBodyClass('hide-registration-note');
    }
}
