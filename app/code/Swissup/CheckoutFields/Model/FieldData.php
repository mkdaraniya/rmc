<?php
namespace Swissup\CheckoutFields\Model;

class FieldData implements \Swissup\CheckoutFields\Api\Data\FieldDataInterface
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string|array
     */
    private $value;

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $code;
    }

    /**
     * @inheritdoc
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $label;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $value;
    }
}
