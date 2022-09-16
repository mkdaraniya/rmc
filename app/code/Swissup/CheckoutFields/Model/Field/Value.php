<?php
namespace Swissup\CheckoutFields\Model\Field;

use Swissup\CheckoutFields\Api\Data\FieldValueInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Value extends AbstractModel implements FieldValueInterface, IdentityInterface
{
    /**
     * cache tag
     */
    const CACHE_TAG = 'swissup_checkout_field_value';

    /**
     * @var string
     */
    protected $_cacheTag = 'swissup_checkout_field_value';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'swissup_checkout_field_value';

    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Swissup\CheckoutFields\Model\ResourceModel\Field\Value');
    }

    /**
     * Get value_id
     *
     * return int
     */
    public function getValueId()
    {
        return $this->getData(self::VALUE_ID);
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
     * Get store_id
     *
     * return int
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Get quote_id
     *
     * return int
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * Get order_id
     *
     * return int
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Get value
     *
     * return string
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * Set value_id
     *
     * @param int $valueId
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setValueId($valueId)
    {
        return $this->setData(self::VALUE_ID, $valueId);
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
     * Set store_id
     *
     * @param int $storeId
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Set quote_id
     *
     * @param int $quoteId
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * Set order_id
     *
     * @param int $orderId
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Set value
     *
     * @param string $value
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * Load by quote_id, field_id and store_id
     *
     * @param int $quoteId
     * @param int $fieldId
     * @param int $storeId
     * @return $this
     */
    public function loadByQuoteFieldAndStore($quoteId, $fieldId, $storeId)
    {
        $this->_getResource()->loadByQuoteFieldAndStore($this, $quoteId, $fieldId, $storeId);
        return $this;
    }

    /**
     * Load by order_id, field_id and store_id
     *
     * @param int $orderId
     * @param int $fieldId
     * @param int $storeId
     * @return $this
     */
    public function loadByOrderFieldAndStore($orderId, $fieldId, $storeId)
    {
        $this->_getResource()->loadByOrderFieldAndStore($this, $orderId, $fieldId, $storeId);
        return $this;
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getValueId()];
    }
}
