<?php
namespace Swissup\DeliveryDate\Block\Adminhtml\Form\Renderer\Config\DateFields;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Year extends Select
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
            $options[] = [
                'value' => 0,
                'label' => 'Yearly'
            ];
            for ($i = -1; $i < 6; $i++) {
                $year = (new \DateTime())->modify($i . ' years')->format('Y');
                $options[] = [
                    'value' => $year,
                    'label' => $year
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
