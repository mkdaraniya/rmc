<?php
namespace Swissup\DeliveryDate\Plugin\Checkout\Model;

use Magento\Quote\Model\QuoteRepository;
use Magento\Checkout\Model\ShippingInformationManagement as Management;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\NoSuchEntityException;

use Swissup\DeliveryDate\Model\DeliverydateFactory;
use Swissup\DeliveryDate\Helper\Data as DataHelper;

class ShippingInformationManagement
{
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var DeliverydateFactory
     */
    protected $deliverydateFactory;

    /**
     * @var \Swissup\DeliveryDate\Helper\Data
     */
    public $dataHelper;

    /**
     * @param QuoteRepository     $quoteRepository
     * @param DeliverydateFactory $deliverydateFactory
     * @param DataHelper $dataHelper
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        DeliverydateFactory $deliverydateFactory,
        DataHelper $dataHelper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->deliverydateFactory = $deliverydateFactory;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param Management $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        Management $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        if (!$this->dataHelper->isEnabled()) {
            return;
        }

        $date = null;
        $time = null;
        $shippingMethod = $addressInformation->getShippingCarrierCode()
            . '_'
            . $addressInformation->getShippingMethodCode();

        $isTimeRequired = $this->dataHelper->isTimeRequired($shippingMethod);
        $isDateRequired = $this->dataHelper->isDateRequired($shippingMethod);

        $extAttributes = $addressInformation->getExtensionAttributes();
        if ($extAttributes) {
            $time = $extAttributes->getDeliveryTime();
            if (!$time && $isTimeRequired) {
                throw new StateException(__('Delivery Time is required'));
            }

            if ($time && !in_array($time, $this->dataHelper->getTimeOptions(true))) {
                throw new NoSuchEntityException(__('Invalid Delivery Time value'));
            }

            $date = $extAttributes->getDeliveryDate();
            if (!$date && $isDateRequired) {
                throw new StateException(__('Delivery Date is required'));
            }
            $date = $this->dataHelper->formatMySqlDateTime($date);

            if (!$this->dataHelper->isValid($date)) {
                throw new StateException(__('Invalid Delivery Date value'));
            }
        } elseif ($isTimeRequired || $isDateRequired) {
            throw new StateException(__('Delivery Date is required'));
        }

        $quote = $this->quoteRepository->getActive($cartId);
        $modelDeliveryDate = $this->deliverydateFactory
            ->create()
            ->loadByQuoteId($quote->getId());

        if ($date || $time) {
            $modelDeliveryDate
                ->setDate($date)
                ->setTimerange($time)
                ->setQuoteId($quote->getId())
                ->save();
        } elseif ($modelDeliveryDate->getId()) {
            $modelDeliveryDate->delete();
        }
    }
}
