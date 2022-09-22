<?php

namespace Swissup\Firecheckout\Helper\Config;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Shipping extends AbstractHelper
{
    /**
     * @var string
     */
    const CONFIG_PATH_DEFAULT_SHIPPING_METHOD = 'firecheckout/shipping/default_method';

    /**
     * @var string
     */
    const CONFIG_PATH_DEFAULT_SHIPPING_METHOD_CODE = 'firecheckout/shipping/default_method_code';

    /**
     * @var string
     */
    const CONFIG_PATH_HIDE_SHIPPING_METHODS = 'firecheckout/shipping/hide_methods';

    /**
     * @var string
     */
    const CONFIG_PATH_SORT_SHIPPING_METHODS_BY_PRICE = 'firecheckout/shipping/sort_by_price';

    /**
     * @var \Swissup\Firecheckout\Helper\Data $firecheckoutHelper
     */
    private $firecheckoutHelper;

    /**
     * @param Context $context
     * \Swissup\Firecheckout\Helper\Data $firecheckoutHelper
     */
    public function __construct(
        Context $context,
        \Swissup\Firecheckout\Helper\Data $firecheckoutHelper
    ) {
        parent::__construct($context);

        $this->firecheckoutHelper = $firecheckoutHelper;
    }

    /**
     * @return string
     */
    public function getDefaultShippingMethod()
    {
        $method = $this->firecheckoutHelper->getConfigValue(
            self::CONFIG_PATH_DEFAULT_SHIPPING_METHOD_CODE,
            ScopeInterface::SCOPE_WEBSITE
        );

        if (!$method) {
            $method = $this->firecheckoutHelper->getConfigValue(
                self::CONFIG_PATH_DEFAULT_SHIPPING_METHOD,
                ScopeInterface::SCOPE_WEBSITE
            );
        }

        return $method;
    }

    /**
     * @return boolean
     */
    public function getHideShippingMethods()
    {
        return (bool) $this->firecheckoutHelper->getConfigValue(
            self::CONFIG_PATH_HIDE_SHIPPING_METHODS,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return boolean
     */
    public function getSortShippingMethodsByPrice()
    {
        return (bool) $this->firecheckoutHelper->getConfigValue(
            self::CONFIG_PATH_SORT_SHIPPING_METHODS_BY_PRICE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
