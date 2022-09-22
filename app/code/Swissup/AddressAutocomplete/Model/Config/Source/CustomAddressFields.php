<?php

namespace Swissup\AddressAutocomplete\Model\Config\Source;

use Magento\Customer\Api\AddressMetadataInterface;

class CustomAddressFields implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Form\Attribute\Collection
     */
    private $collection;

    /**
     * @param \Magento\Customer\Model\ResourceModel\Form\Attribute\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Form\Attribute\CollectionFactory $collectionFactory
    ) {
        $this->collection = $collectionFactory->create();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        $this->collection
            ->addFieldToFilter('frontend_input', ['neq' => 'hidden'])
            ->setEntityType(AddressMetadataInterface::ENTITY_TYPE_ADDRESS)
            ->addFormCodeFilter('adminhtml_customer_address')
            ->addFieldToFilter('is_user_defined', 1);

        foreach ($this->collection as $item) {
            $result[] = [
                'value' => 'custom_attributes[' . $item->getAttributeCode() . ']',
                'label' => $item->getFrontendLabel() . ' [' . $item->getAttributeCode() . ']',
            ];
        }

        return $result;
    }
}
