<?php

namespace Swissup\DeliveryDate\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Config\Model\Config\Source\Locale\Weekdays;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Html\Select;
use Swissup\DeliveryDate\Block\Adminhtml\Form\Renderer\Config\Time;

class ProcessingDayExceptions extends AbstractFieldArray
{
    private $memo = [];

    private function getWeekdaySource(): Weekdays
    {
        return ObjectManager::getInstance()->get(Weekdays::class);
    }

    protected function getWeekdayRenderer(): Select
    {
        if (!isset($this->memo['weekday'])) {
            $this->memo['weekday'] = $this->getLayout()
                ->createBlock(Select::class)
                ->setIsRenderToJsTemplate(true)
                ->setOptions($this->getWeekdaySource()->toOptionArray())
                ->setName($this->_getCellInputElementName('weekday'));
        }

        return $this->memo['weekday'];
    }

    protected function getTimeRenderer(): Time
    {
        if (!isset($this->memo['time'])) {
            $this->memo['time'] = $this->getLayout()->createBlock(Time::class);
        }

        return $this->memo['time'];
    }

    protected function _prepareToRender()
    {
        $this->addColumn('weekday', [
            'label' => __('Day'),
            'renderer' => $this->getWeekdayRenderer()
        ]);

        $this->addColumn('time', [
            'label' => __('Time'),
            'renderer' => $this->getTimeRenderer()
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Exception');
    }

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options = [];

        if ($data = $row->getData('weekday')) {
            $renderer = $this->getWeekdayRenderer();
            $options['option_' . $renderer->calcOptionHash($data)] = 'selected="selected"';
        }

        if ($data = $row->getData('time')) {
            $renderer = $this->getTimeRenderer();

            foreach (['hour', 'minute'] as $type) {
                $hash = $renderer->getElement($type)->calcOptionHash($data[$type]);
                $options['option_' . $hash] = 'selected="selected"';
            }
        }

        $row->setData('option_extra_attrs', $options);
    }
}
