<?php
namespace Swissup\CheckoutFields\Api\Data;

interface FieldInterface
{
    CONST FIELD_ID = 'field_id';
    CONST ATTRIBUTE_CODE = 'attribute_code';
    CONST FRONTEND_INPUT = 'frontend_input';
    CONST FRONTEND_LABEL = 'frontend_label';
    CONST IS_REQUIRED = 'is_required';
    CONST SORT_ORDER = 'sort_order';
    CONST IS_ACTIVE = 'is_active';
    CONST DEFAULT_VALUE = 'default_value';
    CONST CREATED_AT = 'created_at';
    CONST UPDATED_AT = 'updated_at';
    CONST IS_USED_IN_GRID = 'is_used_in_grid';

    /**
     * Get field_id
     *
     * return int
     */
    public function getFieldId();

    /**
     * Get attribute_code
     *
     * return string
     */
    public function getAttributeCode();

    /**
     * Get frontend_input
     *
     * return string
     */
    public function getFrontendInput();

    /**
     * Get frontend_label
     *
     * return string
     */
    public function getFrontendLabel();

    /**
     * Get is_required
     *
     * return int
     */
    public function getIsRequired();

    /**
     * Get sort_order
     *
     * return int
     */
    public function getSortOrder();

    /**
     * Get is_active
     *
     * return int
     */
    public function getIsActive();

    /**
     * Get default value for the element.
     *
     * @return string|null
     */
    public function getDefaultValue();

    /**
     * Get created_at
     *
     * return string
     */
    public function getCreatedAt();

    /**
     * Get updated_at
     *
     * return string
     */
    public function getUpdatedAt();

    /**
     * Get is_used_in_grid
     *
     * return int
     */
    public function getIsUsedInGrid();


    /**
     * Set field_id
     *
     * @param int $fieldId
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setFieldId($fieldId);

    /**
     * Set attribute_code
     *
     * @param sting $attributeCode
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setAttributeCode($attributeCode);

    /**
     * Set frontend_input
     *
     * @param string $frontendInput
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setFrontendInput($frontendInput);

    /**
     * Set frontend_label
     *
     * @param string $frontendLabel
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setFrontendLabel($frontendLabel);

    /**
     * Set is_required
     *
     * @param int $isRequired
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setIsRequired($isRequired);

    /**
     * Set sort_order
     *
     * @param int $sortOrder
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Set is_active
     *
     * @param int $isActive
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setIsActive($isActive);

    /**
     * Set default value for the element.
     *
     * @param string $defaultValue
     * @return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setDefaultValue($defaultValue);

    /**
     * Set is_used_in_grid
     *
     * @param int $isUsedInGrid
     * return \Swissup\CheckoutFields\Api\Data\FieldInterface
     */
    public function setIsUsedInGrid($isUsedInGrid);
}
