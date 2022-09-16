<?php

namespace Swissup\Firecheckout\Helper;

use Swissup\Firecheckout\Model\Config\Source\FormStyle;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var string
     */
    const CONFIG_PATH_ENABLED = 'firecheckout/general/enabled';

    /**
     * @var string
     */
    const CONFIG_PATH_URL_PATH = 'firecheckout/general/url_path';

    /**
     * @var string
     */
    const CONFIG_PATH_LAYOUT = 'firecheckout/general/layout';

    /**
     * @var string
     */
    const CONFIG_PATH_REDIRECT = 'firecheckout/general/redirect_to_checkout';

    /**
     * @var string
     */
    const CONFIG_PATH_PAGE_LAYOUT = 'firecheckout/design/page_layout';

    /**
     * @var string
     */
    const CONFIG_PATH_THEME = 'firecheckout/design/theme';

    /**
     * @var string
     */
    const CONFIG_PATH_FORM_STYLE = 'firecheckout/design/form_style';

    /**
     * @var string
     */
    const CONFIG_PATH_HIDE_LABELS = 'firecheckout/design/hide_labels';

    /**
     * @var string
     */
    const CONFIG_PATH_USE_TOOLTIPS = 'firecheckout/design/use_tooltips';

    /**
     * Retrieve isEnabled flag
     *
     * @return boolean
     */
    public function isFirecheckoutEnabled()
    {
        return $this->getConfigValue(self::CONFIG_PATH_ENABLED);
    }

    /**
     * Retrieve firecheckout url path
     *
     * @return string
     */
    public function getFirecheckoutUrlPath()
    {
        return $this->getConfigValue(
            self::CONFIG_PATH_URL_PATH,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Retrieve flag isRedirectToFirecheckout
     *
     * @return boolean
     */
    public function isRedirectToFirecheckoutEnabled()
    {
        return $this->getConfigValue(
            self::CONFIG_PATH_REDIRECT,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Check is we are currenlty on the standard checkout page
     *
     * @return boolean
     */
    public function isOnStandardCheckoutPage()
    {
        return $this->_getRequest()->getRouteName() === 'checkout'
            && $this->_getRequest()->getControllerName() === 'index';
    }

    /**
     * Check is we are currenlty on the firecheckout page
     *
     * @return boolean
     */
    public function isOnFirecheckoutPage()
    {
        return $this->_getRequest()->getRouteName() === 'firecheckout';
    }

    /**
     * Check is we are currenlty on the success page
     *
     * @return boolean
     */
    public function isOnSuccessPage()
    {
        return $this->_getRequest()->getRouteName() === 'checkout'
            && $this->_getRequest()->getActionName() === 'success';
    }

    /**
     * Get Firecheckout page url
     *
     * @return string
     */
    public function getFirecheckoutUrl()
    {
        return $this->_urlBuilder->getUrl(
            $this->getFirecheckoutUrlPath(),
            ['_secure' => true]
        );
    }

    /**
     * Get Firecheckout layout name
     *
     * @return string
     */
    public function getFirecheckoutLayout()
    {
        return $this->getConfigValue(self::CONFIG_PATH_LAYOUT);
    }

    /**
     * Get Firecheckout layout class name
     *
     * @return array
     */
    public function getLayoutClassNames()
    {
        $result = explode(' ', $this->getFirecheckoutLayout());
        $result[] = $this->isMultistepLayout() ? 'fc-multistep' : 'fc-onestep';
        return $result;
    }

    /**
     * Get page layout config
     *
     * @return string
     */
    public function getPageLayout()
    {
        return $this->getConfigValue(self::CONFIG_PATH_PAGE_LAYOUT);
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return $this->getConfigValue(self::CONFIG_PATH_THEME);
    }

    /**
     * Get page layout config
     *
     * @return string
     */
    public function getFormStyle()
    {
        return $this->getConfigValue(self::CONFIG_PATH_FORM_STYLE);
    }

    /**
     * Get hide_labels flag
     *
     * @return boolean
     */
    public function getHideLabels()
    {
        $value = $this->getConfigValue(self::CONFIG_PATH_HIDE_LABELS);

        if ($value) {
            $formStyle = $this->getFormStyle();
            if ($formStyle === FormStyle::HORIZONTAL) {
                $value = false;
            }
        }

        return $value;
    }

    /**
     * Get is visible labels
     *
     * @return boolean
     */
    public function getShowLabels()
    {
        return !$this->getHideLabels();
    }

    /**
     * Check if layout is multistep
     *
     * @return boolean
     */
    public function isMultistepLayout()
    {
        return $this->getFirecheckoutLayout() === 'firecheckout-col1-set';
    }

    /**
     * Check if email on the separate step
     *
     * @return boolean
     */
    public function isEmailOnSeparateStep()
    {
        return $this->isMultistepLayout()
            && $this->getConfigValue('firecheckout/design/email_on_separate_step');
    }

    /**
     * Check if layout is onecolumn
     *
     * @return boolean
     */
    public function isOnecolumnLayout()
    {
        return strpos($this->getFirecheckoutLayout(), 'firecheckout-col1-set') !== false;
    }

    /**
     * Get if progress bar should be disabled
     *
     * @return boolean
     */
    public function getDisableProgressBar()
    {
        return !$this->isMultistepLayout();
    }

    /**
     * Get if tippy tooltips should be disabled
     *
     * @return boolean
     */
    public function getDisableTooltips()
    {
        return !$this->getConfigValue(self::CONFIG_PATH_USE_TOOLTIPS);
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
}
