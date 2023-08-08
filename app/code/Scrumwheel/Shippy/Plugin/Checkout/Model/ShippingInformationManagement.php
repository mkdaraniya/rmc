<?php
namespace Scrumwheel\Shippy\Plugin\Checkout\Model;

use Magento\Framework\App\RequestInterface as Request;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\QuoteRepository;

class ShippingInformationManagement
{
    protected $quoteRepository;
    protected $resultForwardFactory;
    protected $layoutFactory;
    protected $cart;
    protected $checkoutSession;

    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        LayoutFactory $layoutFactory,
        Cart $cart,
        Session $checkoutSession,
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->layoutFactory = $layoutFactory;
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
    }
    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/order_observer_.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("modle Call");

        // $checkVal = Request::getParam('account_number');
        // $quoteId = $this->checkoutSession->getQuoteId();
        // $quote = $this->quoteRepository->get($quoteId);
        // $quote->setAccountNumber($checkVal);
        // $quote->save();

        // $logger->info("field : ".$checkVal);

        // $this->quoteRepository->setAccountNumber($customfield);
    }
}