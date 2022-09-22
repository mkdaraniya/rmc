<?php
namespace Swissup\CheckoutSuccess\Model\Config\Source;

class AvailableBlocks implements
    \Magento\Framework\Data\OptionSourceInterface,
    \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * List of available blocks on success page
     *
     * @var array
     */
    protected $options = [];

    /**
     * @param array $options
     */
    public function __construct(
        array $options
    ) {
        $this->options = $options;
    }


    /**
     * Get options as array of [value, label]
     *
     * @return array
     */
    public function toOptionArray()
    {
        $arr = [];
        foreach ($this->toOptions() as $key => $value) {
            if (is_array($value)) {
                $value = $value['label'] ?? 'undefined';
            }

            $arr[] = ['value' => $key, 'label' =>$value];
        }

        return $arr;
    }

    public function toOptions()
    {
        return $this->options;
    }
}
