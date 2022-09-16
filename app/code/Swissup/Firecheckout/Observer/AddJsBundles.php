<?php

namespace Swissup\Firecheckout\Observer;

use \Magento\Framework\App\State as AppState;

class AddJsBundles implements \Magento\Framework\Event\ObserverInterface
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
     * @var \Swissup\Firecheckout\Helper\Config\JsBuild
     */
    private $jsBuildHelper;

    /**
     * @var \Swissup\Firecheckout\Model\RequireJs\JsBuildFactory
     */
    private $jsBuildFactory;

    /**
     * @var \Magento\Framework\View\Asset\ConfigInterface
     */
    private $bundleConfig;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \Swissup\Firecheckout\Helper\Data $helper
     * @param \Swissup\Firecheckout\Helper\Config\JsBuild $jsBuildHelper
     * @param \Swissup\Firecheckout\Model\RequireJs\JsBuildFactory $jsBuildFactory
     * @param \Magento\Framework\View\Asset\ConfigInterface $bundleConfig
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        \Magento\Framework\View\Page\Config $pageConfig,
        \Swissup\Firecheckout\Helper\Data $helper,
        \Swissup\Firecheckout\Helper\Config\JsBuild $jsBuildHelper,
        \Swissup\Firecheckout\Model\RequireJs\JsBuildFactory $jsBuildFactory,
        \Magento\Framework\View\Asset\ConfigInterface $bundleConfig,
        \Magento\Framework\App\State $appState
    ) {
        $this->pageConfig = $pageConfig;
        $this->helper = $helper;
        $this->jsBuildHelper = $jsBuildHelper;
        $this->jsBuildFactory = $jsBuildFactory;
        $this->bundleConfig = $bundleConfig;
        $this->appState = $appState;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->jsBuildHelper->isEnabled() || !$this->helper->isOnFirecheckoutPage()) {
            return;
        }

        if ($this->isProduction() && $this->bundleConfig->isBundlingJsFiles()) {
            return;
        }

        $includePaths = $this->jsBuildHelper->getIncludePaths();
        if (!$includePaths) {
            return;
        }

        $jsbuilds = [
            'all' => $includePaths,
        ];

        $assets = $this->pageConfig->getAssetCollection();
        $build = false;
        $after = \Magento\Framework\RequireJs\Config::REQUIRE_JS_FILE_NAME;
        foreach ($jsbuilds as $name => $paths) {
            $build = $this->createJsBuild($name, $paths);
            $asset = $build->getAsset();
            $assets->insert($asset->getFilePath(), $asset, $after);
            $after = $asset->getFilePath();
        }

        if ($build) {
            $asset = $build->createStaticJsAsset();
            $assets->insert($asset->getFilePath(), $asset, $after);
        }
    }

    private function createJsBuild($name, $paths)
    {
        return $this->jsBuildFactory->create([
                'name' => $name,
                'paths' => $paths,
            ])
            ->publishIfNotExist();
    }

    private function isProduction()
    {
        return $this->appState->getMode() === AppState::MODE_PRODUCTION;
    }
}
