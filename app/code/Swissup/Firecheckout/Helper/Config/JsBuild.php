<?php

namespace Swissup\Firecheckout\Helper\Config;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;

class JsBuild extends AbstractHelper
{
    /**
     * @var string
     */
    const CONFIG_PATH_ENABLED = 'firecheckout/performance/jsbuild';

    /**
     * @var string
     */
    const CONFIG_PATH_PREFETCH_ENABLED = 'firecheckout/performance/prefetch';

    /**
     * @var string
     */
    const CONFIG_PATH_PATHS = 'firecheckout/performance/jsbuild_paths';

    /**
     * @var \Swissup\Firecheckout\Helper\Data
     */
    private $helper;

    /**
     * @param Context $context
     * \Swissup\Firecheckout\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        \Swissup\Firecheckout\Helper\Data $helper
    ) {
        parent::__construct($context);

        $this->helper = $helper;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return (bool) $this->helper->getConfigValue(self::CONFIG_PATH_ENABLED);
    }

    /**
     * @return boolean
     */
    public function isPrefetchEnabled()
    {
        return (bool) $this->helper->getConfigValue(self::CONFIG_PATH_PREFETCH_ENABLED);
    }

    /**
     * @return array
     */
    public function getIncludePaths()
    {
        $paths = explode("\n", $this->helper->getConfigValue(self::CONFIG_PATH_PATHS));
        return array_filter($paths);
    }
}
