<?php
namespace Swissup\Taxvat\Plugin\Block\Checkout;

class LayoutProcessor
{
    /**
     * @var \Swissup\Taxvat\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface $magentoMetadata
     */
    protected $magentoMetadata;

    /**
     * @param \Swissup\Taxvat\Helper\Data $helper
     * @param \Magento\Framework\App\ProductMetadataInterface $magentoMetadata
     */
    public function __construct(
        \Swissup\Taxvat\Helper\Data $helper,
        \Magento\Framework\App\ProductMetadataInterface $magentoMetadata
    ) {
        $this->helper = $helper;
        $this->magentoMetadata = $magentoMetadata;
    }

    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $jsLayout
    ) {
        if (!$this->helper->canValidateVat()) {
            return $jsLayout;
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']
            ['children']['shipping-step']['children']['shippingAddress']
            ['children']['shipping-address-fieldset']['children']['vat_id'])) {

            $this->addVatTooltip(
                $jsLayout['components']['checkout']['children']['steps']
                ['children']['shipping-step']['children']['shippingAddress']
                ['children']['shipping-address-fieldset']['children']['vat_id']
            );
        }

        // when DisplayBillingOnPaymentMethod is used
        if (isset($jsLayout['components']['checkout']['children']['steps']
            ['children']['billing-step']['children']['payment']['children']
            ['payments-list']['children'])) {

            $this->addVatTooltipToPaymentForms(
                $jsLayout['components']['checkout']['children']['steps']
                ['children']['billing-step']['children']['payment']
                ['children']['payments-list']['children']
            );
        }

        // when DisplayBillingOnPaymentPage is used
        if (isset($jsLayout['components']['checkout']['children']['steps']
            ['children']['billing-step']['children']['payment']['children']
            ['afterMethods']['children']['billing-address-form']['children']
            ['form-fields']['children']['vat_id'])) {

            $this->addVatTooltip(
                $jsLayout['components']['checkout']['children']
                ['steps']['children']['billing-step']['children']['payment']
                ['children']['afterMethods']['children']
                ['billing-address-form']['children']['form-fields']
                ['children']['vat_id']
            );
        }

        return $jsLayout;
    }

    /**
     * Add tooltip to VAT ID field
     *
     * @param  array &$paymentForms
     * @return void
     */
    private function addVatTooltipToPaymentForms(array &$paymentForms)
    {
        foreach ($paymentForms as $key => $values) {
            if (strpos($key, '-form') === false) {
                continue;
            }

            if (!isset($paymentForms[$key]['children']['form-fields']['children']['vat_id'])) {
                continue;
            }

            $this->addVatTooltip(
                $paymentForms[$key]['children']['form-fields']['children']['vat_id']
            );
        }
    }

    /**
     * Add tooltip to VAT ID field
     *
     * @param  array &$vatField
     * @return void
     */
    private function addVatTooltip(array &$vatField)
    {
        if (version_compare($this->magentoMetadata->getVersion(), '2.3.0', '<')) {
            $vatField['config']['tooltip']['description'] =
                __('Please do not enter country code. For example, DE123456789 is wrong, while 123456789 is correct.');
        }

        if ($this->helper->isVatRequired()) {
            $vatField['validation']['required-entry'] = true;
        }
    }
}
