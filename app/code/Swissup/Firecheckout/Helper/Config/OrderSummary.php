<?php

namespace Swissup\Firecheckout\Helper\Config;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;

class OrderSummary extends AbstractHelper
{
    /**
     * @var string
     */
    const CONFIG_PATH_SHOW_ORDER_REVIEW = 'firecheckout/order_summary/show_order_review';

    /**
     * @var string
     */
    const CONFIG_PATH_TITLE = 'firecheckout/order_summary/title';

    /**
     * @var \Swissup\Firecheckout\Helper\Data
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
    public function getTitle()
    {
        return (string) $this->firecheckoutHelper->getConfigValue(self::CONFIG_PATH_TITLE);
    }

    /**
     * @return boolean
     */
    public function isDisabled()
    {
        return !$this->firecheckoutHelper->getConfigValue(self::CONFIG_PATH_SHOW_ORDER_REVIEW);
    }

    /**
     * @return array
     */
    public function getJsConfig()
    {
        return [
            'title' => $this->getTitle(),
        ];
    }
}
