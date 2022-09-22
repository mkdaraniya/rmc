<?php

namespace Swissup\Firecheckout\Observer;

class PrepareLayout implements \Magento\Framework\Event\ObserverInterface
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
     * Add FontAwesome assets according to module config
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isOnFirecheckoutPage()) {
            return;
        }

        switch ($this->helper->getPageLayout()) {
            case 'full':
                $handle = 'firecheckout_layout_full';
                break;
            case 'minimal':
                $handle = 'firecheckout_layout_minimal';
                break;
            case 'empty':
                $handle = 'firecheckout_layout_empty';
                break;
            case 'default';
                return;
            default:
                return;
        }

        $observer->getLayout()->getUpdate()->addHandle($handle);
    }
}
