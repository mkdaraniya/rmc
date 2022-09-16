<?php

namespace Swissup\Taxvat\Plugin\Helper;

class CustomerAddress
{
    /**
     * @var \Swissup\Taxvat\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\Taxvat\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Taxvat\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Customer\Helper\Address $subject
     * @param string $result
     * @param string $attributeCode
     * @return string
     */
    public function afterGetAttributeValidationClass(
        \Magento\Customer\Helper\Address $subject,
        $result,
        $attributeCode
    ) {
        if ($attributeCode !== 'vat_id') {
            return $result;
        }

        if ($this->helper->isVatFieldEnabled() &&
            $this->helper->isVatRequired() &&
            strpos($result, 'required-entry') === false
        ) {
            $result .= ' required-entry';
        }

        return $result;
    }
}
