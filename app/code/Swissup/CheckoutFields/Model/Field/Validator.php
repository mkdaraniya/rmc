<?php
namespace Swissup\CheckoutFields\Model\Field;

class Validator
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Swissup\CheckoutFields\Model\ResourceModel\Field\CollectionFactory
     */
    protected $fieldsCollectionFactory;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Swissup\CheckoutFields\Model\ResourceModel\Field\CollectionFactory $fieldsCollectionFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Swissup\CheckoutFields\Model\ResourceModel\Field\CollectionFactory $fieldsCollectionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->fieldsCollectionFactory = $fieldsCollectionFactory;
    }

    /**
     * Validate that all required fields are filled
     *
     * @param array $fieldsData
     * @return bool
     */
    public function isValid($fieldsData)
    {
        $requiredFields = $this->getRequiredFieldCodes();
        foreach ($requiredFields as $code) {
            if (empty($fieldsData[$code]) && !is_numeric($fieldsData[$code])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get list of required field codes
     *
     * @return string[]
     */
    private function getRequiredFieldCodes()
    {
        $fieldCodes = [];
        $fieldsCollection = $this->fieldsCollectionFactory->create();
        $fieldsCollection->addStoreFilter($this->storeManager->getStore()->getId());
        $fieldsCollection->addIsActiveFilter(1);
        $fieldsCollection->addFieldToFilter('is_required', 1);
        $fieldCodes = $fieldsCollection->getColumnValues('attribute_code');

        return $fieldCodes;
    }
}
