<?php
namespace Swissup\CheckoutCart\Plugin\Block\Checkout;

class LayoutProcessor
{
    /**
     * @var \Swissup\CheckoutCart\Helper\Data $helper
     */
    protected $helper;

    /**
     * @param \Swissup\CheckoutCart\Helper\Data $helper
     */
    public function __construct(
        \Swissup\CheckoutCart\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $jsLayout
    ) {
        if ($this->helper->isEnabled()) {
            if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['cart_items']['children']['details']))
            {
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['cart_items']['children']['details']['config']['template'] = 'Swissup_CheckoutCart/details';
            }
        }

        if ($this->helper->isChangeOrderEnabled()) {
            if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['cart_items']))
            {
                $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['cart_items']['sortOrder'] = 0;
            }
        }

        return $jsLayout;
    }
}
