<?php
namespace Swissup\CheckoutFields\Api\Data;

interface FieldValueInterface
{
    const VALUE_ID = 'value_id';
    const FIELD_ID = 'field_id';
    const STORE_ID = 'store_id';
    const QUOTE_ID = 'quote_id';
    const ORDER_ID = 'order_id';
    const VALUE = 'value';

    /**
     * Get value_id
     *
     * return int
     */
    public function getValueId();

    /**
     * Get field_id
     *
     * return int
     */
    public function getFieldId();

    /**
     * Get store_id
     *
     * return int
     */
    public function getStoreId();

    /**
     * Get quote_id
     *
     * return int
     */
    public function getQuoteId();

    /**
     * Get order_id
     *
     * return int
     */
    public function getOrderId();

    /**
     * Get value
     *
     * return string
     */
    public function getValue();

    /**
     * Set value_id
     *
     * @param int $valueId
     * return \Swissup\CheckoutFields\Api\Data\FieldValueInterface
     */
    public function setValueId($valueId);

    /**
     * Set field_id
     *
     * @param int $fieldId
     * return \Swissup\CheckoutFields\Api\Data\FieldValueInterface
     */
    public function setFieldId($fieldId);

    /**
     * Set store_id
     *
     * @param int $storeId
     * return \Swissup\CheckoutFields\Api\Data\FieldValueInterface
     */
    public function setStoreId($storeId);

    /**
     * Set quote_id
     *
     * @param int $quoteId
     * return \Swissup\CheckoutFields\Api\Data\FieldValueInterface
     */
    public function setQuoteId($quoteId);

    /**
     * Set order_id
     *
     * @param int $orderId
     * return \Swissup\CheckoutFields\Api\Data\FieldValueInterface
     */
    public function setOrderId($orderId);

    /**
     * Set value
     *
     * @param string $value
     * return \Swissup\CheckoutFields\Api\Data\FieldValueInterface
     */
    public function setValue($value);
}
