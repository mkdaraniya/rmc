<?php

namespace Swissup\Firecheckout\Model;

class ShippingAddressManagement implements \Swissup\Firecheckout\Api\ShippingAddressManagementInterface
{
    /**
     * @var \Magento\Checkout\Api\ShippingInformationManagementInterface
     */
    private $shippingInformationManagement;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @var \Magento\Checkout\Model\PaymentDetailsFactory
     */
    private $paymentDetailsFactory;

    /**
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface
     */
    private $cartTotalsRepository;

    /**
     * @param \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingInformationManagement
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository
     */
    public function __construct(
        \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingInformationManagement,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository
    ) {
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->quoteRepository = $quoteRepository;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->paymentDetailsFactory = $paymentDetailsFactory;
        $this->cartTotalsRepository = $cartTotalsRepository;
    }

    /**
     * This method is used to retrieve updated payment methods list,
     * when changing shipping address.
     *
     * It's a copy of parent::saveShippingInformation method with supressed validation
     */
    public function saveShippingAddress(
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        try {
            // Street should be a string.
            // Fixes invalid logic in Magento\Customer\Model\Address\AbstractAddress@setData
            $street = $addressInformation->getShippingAddress()->getData('street');
            if (is_array($street)) {
                // logic is taken from Magento\Customer\Model\Address\AbstractAddress@_implodeArrayValues
                $addressInformation->getShippingAddress()->setData(
                    'street',
                    trim(implode("\n", $street))
                );
            }

            return $this->shippingInformationManagement
                ->saveAddressInformation($cartId, $addressInformation);
        } catch (\Magento\Framework\Exception\StateException $e) {
            // supress all validation errors - we need to regenerate payments list

            // getPaymentDetails works if prepareShippingAssignment called before,
            // but it is private, so we use Reflection to call it.
            $quote = $this->quoteRepository->getActive($cartId);
            $carrierCode = $addressInformation->getShippingCarrierCode();
            $address = $addressInformation->getShippingAddress();
            if ($address) {
                $address->setLimitCarrier($carrierCode);
            }
            $methodCode = $addressInformation->getShippingMethodCode();

            $reflectedClass = new \ReflectionClass($this->shippingInformationManagement);
            $method = $reflectedClass->getMethod('prepareShippingAssignment');
            $method->setAccessible(true);
            $method->invoke(
                $this->shippingInformationManagement,
                $quote,
                $address,
                $carrierCode . '_' . $methodCode
            );

            try {
                $this->quoteRepository->save($quote);
            } catch (\Exception $e) {
                // $this->logger->critical($e);
            }
        } catch (\Exception $e) {
            // supress all validation errors - we need to regenerate payments list.

            // prepareShippingAssignment was already called, so we need
            // to return payment methods only.
        }

        $paymentDetails = $this->paymentDetailsFactory->create();
        $paymentDetails->setPaymentMethods($this->paymentMethodManagement->getList($cartId));
        $paymentDetails->setTotals($this->cartTotalsRepository->get($cartId));
        return $paymentDetails;
    }
}
