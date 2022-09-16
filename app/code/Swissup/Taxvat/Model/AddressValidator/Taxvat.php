<?php

namespace Swissup\Taxvat\Model\AddressValidator;

use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Address\ValidatorInterface;

class Taxvat implements ValidatorInterface
{
    /**
     * @var \Swissup\Taxvat\Helper\Data
     */
    private $helper;

    /**
     * @var \Swissup\Taxvat\Model\Validator
     */
    private $validator;

    /**
     * @param \Swissup\Taxvat\Model\Validator $validator
     */
    public function __construct(
        \Swissup\Taxvat\Helper\Data $helper,
        \Swissup\Taxvat\Model\Validator $validator
    ) {
        $this->helper = $helper;
        $this->validator = $validator;
    }

    /**
     * @param AbstractAddress $address
     * @return array
     */
    public function validate(AbstractAddress $address)
    {
        if (!$this->helper->canValidateVat()) {
            return [];
        }

        $errors = [];

        if (!$this->helper->validateAddress($address)) {
            $errors[] = __('Please enter a valid VAT number.');
        }

        return $errors;
    }
}
