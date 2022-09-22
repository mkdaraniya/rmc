<?php
namespace Swissup\CheckoutFields\Model;

use Swissup\CheckoutFields\Api\Data\FieldInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Field extends \Magento\Framework\Model\AbstractModel implements FieldInterface, IdentityInterface
{
    /**
     * cache tag
     */
    const CACHE_TAG = 'checkout_field';

    /**
     * @var string
     */
    protected $_cacheTag = 'checkout_field';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'checkout_field';

    /**
     * Frontend instance
     *
     * @var \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
     */
    protected $_frontend;

    /**
     * Source instance
     *
     * @var \Swissup\CheckoutFields\Model\ResourceModel\Field\Option\Collection
     */
    protected $source;

    /**
     * Checkout field options collection factory
     * @var \Swissup\CheckoutFields\Model\ResourceModel\Field\Option\CollectionFactory
     */
    protected $fieldOptionsCollectionFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Source\BooleanFactory
     */
    protected $booleanSourceFactory;

    /**
     * Frontend factory
     *
     * @var \Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontendFactory
     */
    protected $defaultFrontendFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontendFactory $defaultFrontendFactory
     * @param \Swissup\CheckoutFields\Model\ResourceModel\Field\Option\CollectionFactory $fieldOptionsCollectionFactory
     * @param \Magento\Eav\Model\Entity\Attribute\Source\BooleanFactory $booleanSourceFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontendFactory $defaultFrontendFactory,
        \Swissup\CheckoutFields\Model\ResourceModel\Field\Option\CollectionFactory $fieldOptionsCollectionFactory,
        \Magento\Eav\Model\Entity\Attribute\Source\BooleanFactory $booleanSourceFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->defaultFrontendFactory = $defaultFrontendFactory;
        $this->fieldOptionsCollectionFactory = $fieldOptionsCollectionFactory;
        $this->booleanSourceFactory = $booleanSourceFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\CheckoutFields\Model\ResourceModel\Field');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getFieldId()];
    }

    /**
     * Get field_id
     *
     * return int
     */
    public function getFieldId()
    {
        return $this->getData(self::FIELD_ID);
    }

    /**
     * Get attribute_code
     *
     * return string
     */
    public function getAttributeCode()
    {
        return $this->getData(self::ATTRIBUTE_CODE);
    }

    /**
     * Get frontend_input
     *
     * return string
     */
    public function getFrontendInput()
    {
        return $this->getData(self::FRONTEND_INPUT);
    }

    /**
     * Get frontend_label
     *
     * return string
     */
    public function getFrontendLabel()
    {
        return $this->getData(self::FRONTEND_LABEL);
    }

    /**
     * Return frontend label for default store
     *
     * @return string|null
     */
    public function getDefaultFrontendLabel()
    {
        return $this->getFrontendLabel();
    }

    /**
     * Get is_required
     *
     * return int
     */
    public function getIsRequired()
    {
        return $this->getData(self::IS_REQUIRED);
    }

    /**
     * Get sort_order
     *
     * return int
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * Get is_active
     *
     * return int
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * Get default value for the element.
     *
     * @return string|null
     */
    public function getDefaultValue()
    {
        return $this->getData(self::DEFAULT_VALUE);
    }

    /**
     * Get created_at
     *
     * return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Get updated_at
     *
     * return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Get is_used_in_grid
     *
     * return int
     */
    public function getIsUsedInGrid()
    {
        return $this->getData(self::IS_USED_IN_GRID);
    }

    /**
     * Whether it is filterable in grid
     *
     * @return bool|null
     */
    public function getIsFilterableInGrid()
    {
        return true;
    }

    /**
     * Set field_id
     *
     * @param int $fieldId
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setFieldId($fieldId)
    {
        return $this->setData(self::FIELD_ID, $fieldId);
    }

    /**
     * Set attribute_code
     *
     * @param sting $attributeCode
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setAttributeCode($attributeCode)
    {
        return $this->setData(self::ATTRIBUTE_CODE, $attributeCode);
    }

    /**
     * Set frontend_input
     *
     * @param string $frontendInput
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setFrontendInput($frontendInput)
    {
        return $this->setData(self::FRONTEND_INPUT, $frontendInput);
    }

    /**
     * Set frontend_label
     *
     * @param string $frontendLabel
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setFrontendLabel($frontendLabel)
    {
        return $this->setData(self::FRONTEND_LABEL, $frontendLabel);
    }

    /**
     * Set is_required
     *
     * @param int $isRequired
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setIsRequired($isRequired)
    {
        return $this->setData(self::IS_REQUIRED, $isRequired);
    }

    /**
     * Set sort_order
     *
     * @param int $sortOrder
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Set is_active
     *
     * @param int $isActive
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Set default value for the element.
     *
     * @param string $defaultValue
     * @return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setDefaultValue($defaultValue)
    {
        return $this->setData(self::DEFAULT_VALUE, $defaultValue);
    }

    /**
     * Set is_used_in_grid
     *
     * @param int $isUsedInGrid
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setIsUsedInGrid($isUsedInGrid)
    {
        return $this->setData(self::IS_USED_IN_GRID, $isUsedInGrid);
    }

    /**
     * Receive page store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    /**
     * Retrieve frontend instance
     *
     * @return \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
     */
    public function getFrontend()
    {
        if (empty($this->_frontend)) {
            $this->_frontend = $this->defaultFrontendFactory->create()->setAttribute($this);
        }

        return $this->_frontend;
    }

    /**
     * Detect default value using frontend input type
     *
     * @param string $type frontend_input field name
     * @return string default_value field value
     */
    public function getDefaultValueByInput($type)
    {
        $field = '';
        switch ($type) {
            case 'select':
            break;
            case 'multiselect':
                $field = null;
            break;
            case 'text':
                $field = 'default_value_text';
            break;
            case 'textarea':
                $field = 'default_value_textarea';
            break;
            case 'date':
                $field = 'default_value_date';
            break;
            case 'boolean':
                $field = 'default_value_yesno';
            break;
            default:
            break;
        }

        return $field;
    }

    /**
     * Return array of labels of stores
     *
     * @return string[]
     */
    public function getStoreLabels()
    {
        if (!$this->getData('store_labels')) {
            $storeLabel = $this->getResource()->getStoreLabelsByFieldId($this->getId());
            $this->setData('store_labels', $storeLabel);
        }
        return $this->getData('store_labels');
    }

    /**
     * Return store label of field
     *
     * @param int $storeId
     * @return string
     */
    public function getStoreLabel($storeId)
    {
        $labels = $this->getStoreLabels();
        if (isset($labels[$storeId])) {
            return $labels[$storeId];
        } else {
            return $this->getFrontendLabel();
        }
    }

    /**
     * Load field by code
     *
     * @param  string $code
     * @return $this
     * @throws LocalizedException
     */
    public function loadByCode($code)
    {
        $this->getResource()->loadByCode($this, $code);
        return $this;
    }

    /**
     * Whether possible attribute values are retrieved from finite source
     *
     * @return bool
     */
    public function usesSource()
    {
        $input = $this->getFrontendInput();

        return $input === 'select' || $input === 'multiselect' ||
            $input === 'boolean' || $this->getData('source_model') != '';
    }

    /**
     * Retrieve source instance
     *
     * @return \Swissup\CheckoutFields\Model\ResourceModel\Field\Option\Collection
     */
    public function getSource()
    {
        if (empty($this->source)) {
            if ($this->getFrontendInput() == 'boolean') {
                $this->source = $this->booleanSourceFactory->create();
            } else {
                $this->source = $this->fieldOptionsCollectionFactory->create()
                    ->setPositionOrder('asc')
                    ->setAttributeFilter($this->getId())
                    ->setStoreFilter(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
            }
        }

        return $this->source;
    }
}
