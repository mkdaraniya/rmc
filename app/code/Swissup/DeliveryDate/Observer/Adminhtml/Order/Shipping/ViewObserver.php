<?php
namespace Swissup\DeliveryDate\Observer\Adminhtml\Order\Shipping;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

use Swissup\DeliveryDate\Model\DeliverydateFactory;

class ViewObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var bool
     */
    protected $configHelper;

    /**
     *
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        TimezoneInterface $timezone,
        \Swissup\DeliveryDate\Helper\Data $configHelper
    ) {
        $this->timezone = $timezone;
        $this->configHelper = $configHelper;
    }

    public function execute(EventObserver $observer)
    {
        if ($observer->getElementName() != 'order_shipping_view') {
            return;
        }

        if (!$this->configHelper->isEnabled()) {
            return;
        }

        $orderShippingViewBlock = $observer->getLayout()->getBlock($observer->getElementName());
        $order = $orderShippingViewBlock->getOrder();
        $formattedDate = 'N/A';
        $deliveryTime = '';

        $deliveryDate = $order->getExtensionAttributes()->getSwissupDeliveryDate();

        if ($deliveryDate) {
            $date = $deliveryDate->getDate();
            $formattedDate = $this->timezone->formatDate(
                $date,
                \IntlDateFormatter::MEDIUM,
                false
            );
            $deliveryTime = $deliveryDate->getTimerange();
        }

        $deliveryDateBlock = $observer->getLayout()->createBlock(
            'Magento\Framework\View\Element\Template'
        );
        $deliveryDateBlock->setDeliveryDate($formattedDate);
        $deliveryDateBlock->setDeliveryTime($deliveryTime);
        $deliveryDateBlock->setTemplate('Swissup_DeliveryDate::order/shipping/view.phtml');

        $deliveryDateEditBlock = $observer->getLayout()->createBlock(
            'Swissup\DeliveryDate\Block\Adminhtml\DateField'
        );
        $deliveryDateEditBlock->setDeliveryDate($formattedDate);
        $deliveryDateEditBlock->setDeliveryTime($deliveryTime);
        $deliveryDateEditBlock->setTemplate('Swissup_DeliveryDate::date_field.phtml');
        $deliveryDateBlock->setAjaxSaveSubmitUrl($deliveryDateEditBlock->getAjaxSaveSubmitUrl());

        $deliveryDateBlock->setChild('edit_block', $deliveryDateEditBlock);

        $button = $deliveryDateBlock->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'label' => __('Save Delivery Date'),
                'class' => 'action-save action-secondary',
            ]
        );
        $deliveryDateBlock->setChild('submit_button', $button);

        $html = $observer->getTransport()->getOutput() . $deliveryDateBlock->toHtml();

        $observer->getTransport()->setOutput($html);
    }
}
