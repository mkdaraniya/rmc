<?php
namespace Swissup\DeliveryDate\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

use Swissup\DeliveryDate\Block\Adminhtml\Form\Renderer\Config\DateFields\Day;
use Swissup\DeliveryDate\Block\Adminhtml\Form\Renderer\Config\DateFields\Month;
use Swissup\DeliveryDate\Block\Adminhtml\Form\Renderer\Config\DateFields\Year;

class Holidays extends AbstractFieldArray
{
    protected $renderer = [];

    protected function getRenderer($class)
    {
        if (!isset($this->renderer[$class])) {
            $this->renderer[$class] = $this->getLayout()->createBlock(
                $class,
                '',
                ['data' => [
                        'value' => $this->getValue(),
                        'is_render_to_js_template' => true,
                    ]
                ]
            );
        }
        return $this->renderer[$class];
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'name',
            [
            'label' => __('Name'),
            ]
        );
        $this->addColumn(
            'day',
            [
            'label' => __('Day'),
            'renderer' => $this->getRenderer(Day::class)
            ]
        );
        $this->addColumn(
            'month',
            [
            'label' => __('Month'),
            'renderer' => $this->getRenderer(Month::class)
            ]
        );
        $this->addColumn(
            'year',
            [
            'label' => __('Year'),
            'renderer' => $this->getRenderer(Year::class)
            ]
        );
        $this->addColumn(
            'offset',
            [
            'label' => __('Offset'),
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Holiday');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];
        $day = $row->getDay();
        if ($day) {
            $options['option_' . $this->getRenderer(Day::class)->calcOptionHash($day)]
                = 'selected="selected"';
        }

        $month = $row->getMonth();
        if ($month) {
            $options['option_' . $this->getRenderer(Month::class)->calcOptionHash($month)]
                = 'selected="selected"';
        }

        $year = $row->getYear();
        if ($year) {
            $options['option_' . $this->getRenderer(Year::class)->calcOptionHash($year)]
                = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }
}
