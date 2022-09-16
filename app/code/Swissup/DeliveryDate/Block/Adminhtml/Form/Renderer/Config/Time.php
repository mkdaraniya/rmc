<?php

namespace Swissup\DeliveryDate\Block\Adminhtml\Form\Renderer\Config;

use Magento\Framework\View\Element\Template;

class Time extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @var \Magento\Framework\View\Element\Html\Select
     */
    private $hour;

    /**
     * @var \Magento\Framework\View\Element\Html\Select
     */
    private $minute;

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getHourElement()->toHtml()
            . ' : '
            . $this->getMinuteElement()->toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        $this->getHourElement()->setName($value . '[hour]');
        $this->getMinuteElement()->setName($value . '[minute]');
        return $this;
    }

    /**
     * @return \Magento\Framework\View\Element\Html\Select
     */
    public function getElement($type)
    {
        switch ($type) {
            case 'hour':
                return $this->getHourElement();
            case 'minute':
                return $this->getMinuteElement();
            default:
                throw new \Exception(sprintf('Unsupported element type: %s', $type));
        }
    }

    /**
     * @return \Magento\Framework\View\Element\Html\Select
     */
    protected function createSelectElement()
    {
        return $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class,
            '',
            [
                'data' => [
                    'value' => $this->getValue(),
                    'is_render_to_js_template' => true,
                    'extra_params' => 'style="width:72px;min-width:0;"'
                ]
            ]
        );
    }

    /**
     * @return \Magento\Framework\View\Element\Html\Select
     */
    protected function getHourElement()
    {
        if (!$this->hour) {
            $this->hour = $this->createSelectElement();
            $this->hour->setOptions($this->getElementOptions(0, 23));
        }
        return $this->hour;
    }

    /**
     * @return \Magento\Framework\View\Element\Html\Select
     */
    protected function getMinuteElement()
    {
        if (!$this->minute) {
            $this->minute = $this->createSelectElement();
            $this->minute->setOptions($this->getElementOptions(0, 59));
        }
        return $this->minute;
    }

    /**
     * @param  int $from
     * @param  int $to
     * @return array
     */
    protected function getElementOptions($from, $to)
    {
        $result = [];
        for ($i = $from; $i <= $to; $i++) {
            $value = str_pad($i, 2, '0', STR_PAD_LEFT);
            $result[] = [
                'value' => $value,
                'label' => $value,
            ];
        }
        return $result;
    }
}
