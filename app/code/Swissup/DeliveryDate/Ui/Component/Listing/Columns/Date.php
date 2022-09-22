<?php

namespace Swissup\DeliveryDate\Ui\Component\Listing\Columns;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Date extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\Context $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param TimezoneInterface $timezone
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\Context $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        array $components = [],
        array $data = []
    ) {
        $this->timezone = $timezone;
        return parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item['delivery_date'] = $this->getDeliveryDateByItem($item);
            }
        }
        return $dataSource;
    }

    protected function getDeliveryDateByItem($item)
    {
        $result = 'N/A';

        if (!empty($item['delivery_date']) || !empty($item['delivery_time'])) {
            $result = [];

            if (!empty($item['delivery_date'])) {
                $date = new \DateTime($item['delivery_date']);

                $configuration = $this->getConfiguration();
                if (!empty($configuration['timezone'])) {
                    $date = $this->timezone->date($date);
                }

                $dateFormat = $configuration['dateFormat'] ?? 'Y-m-d';
                $result[] = $date->format($dateFormat);
            }

            $result[] = $item['delivery_time'] ?? false;

            $result = array_filter($result);
            $result = implode(' ', $result);
        }

        return $result;
    }
}
