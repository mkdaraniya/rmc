<?php

namespace Swissup\AddressFieldManager\Model\Address\Validator;

use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Address\ValidatorInterface;
use Swissup\AddressFieldManager\Model\ResourceModel\Customer\Form\AddressAttribute\CollectionFactory;

class General implements ValidatorInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryData = null;

    /**
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        \Magento\Directory\Helper\Data $directoryData
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->directoryData = $directoryData;
    }

    public function validate(AbstractAddress $address)
    {
        $errors = [];

        $collection = $this->collectionFactory->create();
        foreach ($collection as $attribute) {
            if (!$attribute->getIsRequired() || !$attribute->getIsVisible()) {
                continue;
            }

            $code  = $attribute->getAttributeCode();
            $value = $address->getData($code);
            if ('street' === $attribute->getAttributeCode()) {
                $value = $address->getStreetLine(1);
            }

            if (!\Zend_Validate::is($value, 'NotEmpty')) {
                $errors[] = __('%fieldName is a required field.', [
                    'fieldName' => $code
                ]);
            }
        }

        $havingOptionalZip = $this->directoryData->getCountriesWithOptionalZip();
        if (!in_array($address->getCountryId(), $havingOptionalZip)
            && !\Zend_Validate::is($address->getPostcode(), 'NotEmpty')
        ) {
            $errors[] = __('%fieldName is a required field.', [
                'fieldName' => 'postcode'
            ]);
        }

        return $errors;
    }
}
