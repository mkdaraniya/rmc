<?php

namespace Swissup\Firecheckout\Plugin\Helper;

class GoogleAnalyticsData
{
    /**
     * @var \Swissup\Firecheckout\Helper\Data
     */
    private $helper;

    /**
     * @var \Swissup\Firecheckout\Helper\Config\GoogleAnalytics
     */
    private $googleAnalyticsHelper;

    /**
     * @param \Swissup\Firecheckout\Helper\Data $helper
     * @param \Swissup\Firecheckout\Helper\Config\GoogleAnalytics $googleAnalyticsHelper
     */
    public function __construct(
        \Swissup\Firecheckout\Helper\Data $helper,
        \Swissup\Firecheckout\Helper\Config\GoogleAnalytics $googleAnalyticsHelper
    ) {
        $this->helper = $helper;
        $this->googleAnalyticsHelper = $googleAnalyticsHelper;
    }

    /**
     * Enable Magento's GA integration on checkout and checkout success pages if
     * it's enabled in firecheckout config.
     *
     * @param \Magento\GoogleAnalytics\Helper\Data $subject
     * @param boolean $result
     * @return boolean
     */
    public function afterIsGoogleAnalyticsAvailable(
        \Magento\GoogleAnalytics\Helper\Data $subject,
        $result
    ) {
        if ($result) {
            return $result;
        }

        if (!$this->googleAnalyticsHelper->isEnabled()) {
            return false;
        }

        if ($this->helper->isOnFirecheckoutPage()) {
            return true;
        }

        return $this->helper->isFirecheckoutEnabled()
            && $this->helper->isOnSuccessPage();
    }
}
