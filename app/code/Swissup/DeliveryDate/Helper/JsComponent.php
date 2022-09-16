<?php

namespace Swissup\DeliveryDate\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;

class JsComponent extends AbstractHelper
{
    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Swissup\DeliveryDate\Model\DeliverydateFactory
     */
    private $deliveryDateFactory;

    /**
     * @var \Swissup\DeliveryDate\Model\Deliverydate
     */
    private $deliveryDate;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @param Context $context
     * @param Data $dataHelper
     * @param \Swissup\DeliveryDate\Model\DeliverydateFactory $deliveryDate
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        \Swissup\DeliveryDate\Model\DeliverydateFactory $deliveryDateFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        parent::__construct($context);

        $this->dataHelper = $dataHelper;
        $this->deliveryDateFactory = $deliveryDateFactory;
        $this->checkoutSession = $checkoutSession;
        $this->assetRepo = $assetRepo;
    }

    /**
     * @return \Swissup\DeliveryDate\Model\Deliverydate
     */
    protected function getDeliveryDate()
    {
        if ($this->deliveryDate === null) {
            $this->deliveryDate = $this->deliveryDateFactory->create()
                ->loadByQuoteId($this->checkoutSession->getQuote()->getId());
        }
        return $this->deliveryDate;
    }

    /**
     * @return array
     */
    public function getContainerConfig()
    {
        return [
            'provider' => 'checkoutProvider',
            'componentDisabled' => !$this->dataHelper->isEnabled(),
            'filterPerShippingMethod' => $this->dataHelper->isFilterPerShippingMethod(),
            'shippingMethods' => $this->dataHelper->getShippingMethods(),
        ];
    }

    /**
     * @return array
     */
    public function getDateFieldConfig()
    {
        $validation = [
            'delivery-date-validate-date' => true,
        ];
        // fix for invalid validation logic, when additional params are set
        if ($this->dataHelper->isDateRequired()) {
            $validation['required-entry'] = true;
        }

        return [
            'label' => $this->dataHelper->getDateLabel(),
            'componentDisabled' => !$this->dataHelper->getDateStatus(),
            'value' => $this->dataHelper->getFormattedDate($this->getDeliveryDate()->getDate()),
            'provider' => 'checkoutProvider',
            'validation' => $validation,
            'excludedWeekdays' => $this->dataHelper->getExcludedWeekdays(),
            'holidays' => $this->dataHelper->getHolidays(),
            'useDefaultDate' => $this->dataHelper->useDefaultDateValue(),
            'options' => [
                'minDate' => $this->dataHelper->getMinDelayDays(),
                'maxDate' => $this->dataHelper->getMaxDelayDays(),
                'dateFormat' => $this->dataHelper->getDateFormat(true),
                'firstDay' => $this->dataHelper->getFirstDay(),
                'showOn' => 'both',
                'buttonImage' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
            ],
        ];
    }

    /**
     * @return array
     */
    public function getTimeFieldConfig()
    {
        return [
            'label' => $this->dataHelper->getTimeLabel(),
            'componentDisabled' => !$this->dataHelper->getTimeStatus(),
            'value' => $this->getDeliveryDate()->getTimerange(),
            'provider' => 'checkoutProvider',
            'validation' => [
                'required-entry' => $this->dataHelper->isTimeRequired(),
                'validate-select' => $this->dataHelper->isTimeRequired(),
            ],
            'options' => $this->dataHelper->getTimeOptions(),
        ];
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    private function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->_getRequest()->isSecure()], $params);
            return $this->assetRepo->getUrlWithParams($fileId, $params);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->_getUrl('', ['_direct' => 'core/index/notFound']);
        }
    }
}
