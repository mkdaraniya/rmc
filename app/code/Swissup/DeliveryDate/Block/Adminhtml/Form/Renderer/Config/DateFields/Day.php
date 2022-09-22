<?php
namespace Swissup\DeliveryDate\Block\Adminhtml\Form\Renderer\Config\DateFields;

use Magento\Framework\View\Element\Html\Select;

// use Magento\Framework\Locale\Bundle\DataBundle;

class Day extends Select
{
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $options = [];
            for ($i = 1; $i < 32; $i++) {
                $options[] = [
                    'value' => $i,
                    'label' => $i
                ];
            }

            $this->setOptions($options);
        }
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
