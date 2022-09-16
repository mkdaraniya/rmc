<?php
namespace Swissup\DeliveryDate\Block\Adminhtml\Form\Renderer\Config\DateFields;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\Locale\Bundle\DataBundle;

class Month extends Select
{
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            // get days and months for en_US locale - calendar will parse exactly in this locale
            $englishMonths = (new DataBundle())->get('en_US')['calendar']['gregorian']['monthNames'];
            $englishMonths = iterator_to_array($englishMonths['format']['abbreviated']);
            $options = [];
            $options[] = [
                'value' => 0,
                'label' => 'Monthly'
            ];
            foreach ($englishMonths as $key => $value) {
                $options[] = [
                    'value' => $key + 1,
                    'label' => $value
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
