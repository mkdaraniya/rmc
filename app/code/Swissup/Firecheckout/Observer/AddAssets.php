<?php

namespace Swissup\Firecheckout\Observer;

class AddAssets implements \Magento\Framework\Event\ObserverInterface
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
     * @param \Swissup\Firecheckout\Helper\Data   $helper
     */
    public function __construct(
        \Magento\Framework\View\Page\Config $pageConfig,
        \Swissup\Firecheckout\Helper\Data $helper
    ) {
        $this->pageConfig = $pageConfig;
        $this->helper = $helper;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isOnFirecheckoutPage()) {
            return;
        }

        $this->pageConfig->addPageAsset(
            'Swissup_Firecheckout::css/firecheckout-' . $this->helper->getTheme() . '.css',
            [
                'attributes' => [
                    'media' => 'screen, print',
                ]
            ]
        );
    }
}
