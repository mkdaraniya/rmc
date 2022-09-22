<?php
namespace Swissup\CheckoutCart\Plugin\Model;

class QuoteItem
{
    /**
     * @var \Swissup\CheckoutCart\Helper\Data
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

    /**
     * @param  \Magento\Quote\Model\Quote\Item $subject
     * @param  array $result
     * @return array
     */
    public function afterToArray(
        \Magento\Quote\Model\Quote\Item $subject,
        array $result
    ) {
        if (!$this->helper->isEnabled()) {
            return $result;
        }

        $item = $subject->getProduct()->getExtensionAttributes()->getStockItem();
        $result['qty_increments'] = $item->getQtyIncrements();

        return $result;
    }
}
