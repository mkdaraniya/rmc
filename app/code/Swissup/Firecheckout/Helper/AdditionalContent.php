<?php

namespace Swissup\Firecheckout\Helper;

use Magento\Customer\Model\Context as CustomerContext;

class AdditionalContent extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var string
     */
    const CONFIG_PATH_CMS_BLOCK_TOP = 'firecheckout/additional_content/top';

    /**
     * @var string
     */
    const CONFIG_PATH_CMS_BLOCK_BOTTOM = 'firecheckout/additional_content/bottom';

    /**
     * @var string
     */
    const CONFIG_PATH_CMS_BLOCK_BELOW_ORDER_SUMMARY = 'firecheckout/additional_content/below_order_summary';

    /**
     * @var string
     */
    const CONFIG_PATH_CMS_BLOCK_BELOW_PLACE_ORDER = 'firecheckout/additional_content/below_place_order';

    /**
     * @var string
     */
    const CONFIG_PATH_CMS_BLOCK_INTRO_POPUP_GUEST = 'firecheckout/additional_content/intro/guest';

    /**
     * @var string
     */
    const CONFIG_PATH_CMS_BLOCK_INTRO_POPUP_REGISTERED = 'firecheckout/additional_content/intro/registered';

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Http\Context   $httpContext
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Checkout\Model\Session       $session
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Checkout\Model\Session $session
    ) {
        $this->httpContext = $httpContext;
        $this->layoutFactory = $layoutFactory;
        $this->session = $session;

        parent::__construct($context);
    }

    /**
     * Get template path to render additional content
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'Swissup_Firecheckout::additional_content.phtml';
    }

    /**
     * Get top cms block
     *
     * @return string
     */
    public function getTopCmsBlockId()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_CMS_BLOCK_TOP,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get bottom cms block
     *
     * @return string
     */
    public function getBottomCmsBlockId()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_CMS_BLOCK_BOTTOM,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get below order summary cms block
     *
     * @return string
     */
    public function getBelowOrderSummaryCmsBlockId()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_CMS_BLOCK_BELOW_ORDER_SUMMARY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get cms block to show below place order button
     *
     * @return string
     */
    public function getBelowPlaceOrderCmsBlockId()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_CMS_BLOCK_BELOW_PLACE_ORDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getIntroPopupCmsBlockId()
    {
        if ($this->session->getFirecheckoutIntroPopupFlag()) {
            return false;
        }

        $id = $this->scopeConfig->getValue(
            $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH) ?
                self::CONFIG_PATH_CMS_BLOCK_INTRO_POPUP_REGISTERED :
                self::CONFIG_PATH_CMS_BLOCK_INTRO_POPUP_GUEST,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($id) {
            $this->session->setFirecheckoutIntroPopupFlag(true);
        }

        return $id;
    }

    /**
     * Render cms block by its identifier or ID
     *
     * @param  mixed $blockId
     * @param  string $blockCss Css class to use in content wrapper
     * @return string
     */
    public function render($blockId, $blockCss = null)
    {
        if (strpos($blockId, '::') !== false) {
            $blockId = call_user_func($blockId);
        }

        if (!$blockId) {
            return '';
        }

        return $this->layoutFactory->create()
            ->createBlock('Magento\Cms\Block\Widget\Block')
            ->setTemplate($this->getTemplate())
            ->setBlockId($blockId)
            ->setBlockCss($blockCss)
            ->toHtml();
    }
}
