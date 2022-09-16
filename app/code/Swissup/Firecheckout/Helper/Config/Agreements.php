<?php

namespace Swissup\Firecheckout\Helper\Config;

use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Swissup\Firecheckout\Model\Config\Source\AgreementsPosition;

class Agreements extends AbstractHelper
{
    /**
     * @var string
     */
    const CONFIG_PATH_TITLE = 'firecheckout/agreements/title';

    /**
     * @var string
     */
    const CONFIG_PATH_POSITION = 'firecheckout/agreements/position';

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
    public function getPosition()
    {
        return $this->firecheckoutHelper->getConfigValue(self::CONFIG_PATH_POSITION);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->firecheckoutHelper->getConfigValue(self::CONFIG_PATH_TITLE);
    }

    /**
     * @return boolean
     */
    public function isMoverDisabled()
    {
        if (!$this->firecheckoutHelper->getConfigValue(AgreementsProvider::PATH_ENABLED)) {
            return true;
        }

        return $this->getPosition() === AgreementsPosition::VALUE_EMTPY;
    }

    /**
     * @return array
     */
    public function getMoverJsConfig()
    {
        return [
            'title' => $this->getTitle(),
            'position' => $this->getPosition(),
        ];
    }
}
