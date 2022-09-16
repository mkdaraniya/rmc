<?php
namespace Swissup\CheckoutFields\Api\Data;

interface FieldDataInterface
{
    /**
     * Get field code
     *
     * @return string
     */
    public function getCode();

    /**
     * Get field label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Get value
     *
     * @return string|array
     */
    public function getValue();

    /**
     * Set field code
     *
     * @param string $code
     * @return string
     */
    public function setCode($code);

    /**
     * Set field label
     *
     * @param string $label
     * @return string
     */
    public function setLabel($label);

    /**
     * Set value
     *
     * @param string|array $value
     * @return string
     */
    public function setValue($value);
}
