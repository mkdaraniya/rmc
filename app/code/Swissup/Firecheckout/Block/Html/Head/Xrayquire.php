<?php

namespace Swissup\Firecheckout\Block\Html\Head;

/**
 * Block responsible for including xrayquire on the page
 */
class Xrayquire extends \Magento\Framework\View\Element\AbstractBlock
{
    const XRAYQUIRE_PATH         = 'Swissup_Firecheckout::js/lib/xrayquire.js';

    const XRAYQUIRE_MAGENTO_PATH = 'Swissup_Firecheckout::js/lib/xrayquire-magento.js';

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfig;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\View\Page\Config $pageConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->pageConfig = $pageConfig;
    }

    /**
     * Include RequireJs configuration as an asset on the page
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if (!$this->getRequest()->getParam('xrayquire')) {
            return;
        }

        $after = \Magento\Framework\RequireJs\Config::REQUIRE_JS_FILE_NAME;
        $assetCollection = $this->pageConfig->getAssetCollection();

        $xrayquire = $this->_assetRepo->createAsset(self::XRAYQUIRE_PATH);
        $assetCollection->insert(
            $xrayquire->getFilePath(),
            $xrayquire,
            $after
        );

        $xrayquireMagento = $this->_assetRepo->createAsset(self::XRAYQUIRE_MAGENTO_PATH);
        $assetCollection->insert(
            $xrayquireMagento->getFilePath(),
            $xrayquireMagento,
            $xrayquire->getFilePath()
        );

        return parent::_prepareLayout();
    }
}
