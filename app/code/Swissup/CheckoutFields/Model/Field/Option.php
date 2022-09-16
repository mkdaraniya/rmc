<?php
namespace Swissup\CheckoutFields\Model\Field;

use Swissup\CheckoutFields\Api\Data\FieldOptionInterface;
use Magento\Framework\Model\AbstractModel;

class Option extends AbstractModel implements FieldOptionInterface
{
    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Swissup\CheckoutFields\Model\ResourceModel\Field\Option');
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getData(FieldOptionInterface::LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getData(FieldOptionInterface::VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(FieldOptionInterface::SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsDefault()
    {
        return $this->getData(FieldOptionInterface::IS_DEFAULT);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreLabels()
    {
        return $this->getData(FieldOptionInterface::STORE_LABELS);
    }
    /**
     * Set option label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        return $this->setData(FieldOptionInterface::LABEL, $label);
    }

    /**
     * Set option value
     *
     * @param string $value
     * @return string
     */
    public function setValue($value)
    {
        return $this->setData(FieldOptionInterface::VALUE, $value);
    }

    /**
     * Set option order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(FieldOptionInterface::SORT_ORDER, $sortOrder);
    }

    /**
     * set is default
     *
     * @param bool $isDefault
     * @return $this
     */
    public function setIsDefault($isDefault)
    {
        return $this->setData(FieldOptionInterface::IS_DEFAULT, $isDefault);
    }

    /**
     * Set option label for store scopes
     *
     * @param \Magento\Eav\Api\Data\AttributeOptionLabelInterface[] $storeLabels
     * @return $this
     */
    public function setStoreLabels(array $storeLabels = null)
    {
        return $this->setData(FieldOptionInterface::STORE_LABELS, $storeLabels);
    }
}
