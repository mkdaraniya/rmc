<?php

namespace Swissup\DeliveryDate\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Swissup\DeliveryDate\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\DeliveryDate\Helper\Data $helper
     */
    public function __construct(\Swissup\DeliveryDate\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'swissup' => [
                'DeliveryDate' => [
                    'enabled' => $this->helper->isEnabled()
                ]
            ]
        ];
    }
}
