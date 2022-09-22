<?php

namespace Swissup\DeliveryDate\Block\Adminhtml\Form\Field;

class TimeOptions extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var array
     */
    protected $renderer = [];

    /**
     * @param  string $type
     * @return \Swissup\DeliveryDate\Block\Adminhtml\Form\Renderer\Config\Time
     */
    protected function getRenderer($type)
    {
        if (!isset($this->renderer[$type])) {
            $this->renderer[$type] = $this->getLayout()->createBlock(
                \Swissup\DeliveryDate\Block\Adminhtml\Form\Renderer\Config\Time::class,
                '',
                [
                    'data' => [
                        'value' => $this->getValue(),
                        'is_render_to_js_template' => true,
                    ]
                ]
            );
        }
        return $this->renderer[$type];
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'from',
            [
                'label' => __('From'),
                'renderer' => $this->getRenderer('from')
            ]
        );
        $this->addColumn(
            'to',
            [
                'label' => __('To'),
                'renderer' => $this->getRenderer('to')
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Time Entry');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options = [];

        foreach (['from', 'to'] as $key) {
            $data = $row->getData($key);
            if (!$data) {
                continue;
            }

            $renderer = $this->getRenderer($key);
            foreach (['hour', 'minute'] as $type) {
                $hash = $renderer->getElement($type)->calcOptionHash($data[$type]);
                $options['option_' . $hash] = 'selected="selected"';
            }
        }

        $row->setData('option_extra_attrs', $options);
    }
}
