<?php

namespace Swissup\Firecheckout\Helper\Config;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Swissup\Firecheckout\Model\Config\Source\PlaceOrderPosition;

class PlaceOrder extends AbstractHelper
{
    /**
     * @var string
     */
    const CONFIG_PATH_POSITION = 'firecheckout/design/place_order_button_position';

    /**
     * @var \Swissup\Firecheckout\Helper\Data $firecheckoutHelper
     */
    private $firecheckoutHelper;

    /**
     * @param Context $context
     * @param \Swissup\Firecheckout\Helper\Data $firecheckoutHelper
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
    public function getPosition()
    {
        if (!$this->firecheckoutHelper->isOnecolumnLayout()) {
            return PlaceOrderPosition::VALUE_EMTPY;
        }

        return $this->firecheckoutHelper->getConfigValue(self::CONFIG_PATH_POSITION);
    }

    /**
     * @return boolean
     */
    public function isMoverDisabled()
    {
        return !$this->getPosition();
    }

    /**
     * @return array
     */
    public function getMoverJsConfig()
    {
        return [
            'position' => $this->getPosition(),
        ];
    }
}
