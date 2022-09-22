<?php
namespace Swissup\CheckoutFields\Plugin\Block\Adminhtml\Order;

class CreateAccountForm
{
    /**
     * @var \Swissup\CheckoutFields\Helper\Data
     */
    protected $helper;

    /**
     * @param \Swissup\CheckoutFields\Helper\Data $helper
     */
    public function __construct(
        \Swissup\CheckoutFields\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param  \Magento\Sales\Block\Adminhtml\Order\Create\Form\Account $subject
     * @param  string $result
     * @return string
     */
    public function afterToHtml(
        \Magento\Sales\Block\Adminhtml\Order\Create\Form\Account $subject,
        $result
    ) {
        if ($this->helper->isEnabled()) {
            $fieldsFormHtml = $subject->getLayout()->createBlock(
                'Swissup\CheckoutFields\Block\Adminhtml\Order\Create\Form',
                'swissup_checkout_fields_form'
            )->setTemplate('Swissup_CheckoutFields::order/create/form.phtml')
            ->setStore($subject->getStore())
            ->toHtml();

            return $result . $fieldsFormHtml;
        }

        return $result;
    }
}
