<?php
namespace Scrumwheel\Shippy\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteFactory;
use Psr\Log\LoggerInterface;
 
class SaveOrderBeforeSalesModelQuote implements ObserverInterface
{
    /**
    * @var \Psr\Log\LoggerInterface
    */
    protected $_logger;
 
    /**
    * @var \Magento\Customer\Model\Session
    */
    protected $quoteFactory;
 
    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
 
    public function __construct(LoggerInterface $logger,
        QuoteFactory $quoteFactory) {
        $this->_logger = $logger;
        $this->quoteFactory = $quoteFactory;
    }
 
    public function execute(Observer $observer)
    {

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/order_observer_.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Observer Call");

        $order = $observer->getOrder();
        $quoteId = $order->getQuoteId();
        $quote  = $this->quoteFactory->create()->load($quoteId);
        $quote->setKaushikOrderComment('1234');
$logger->info(json_encode($_COOKIE));	
if (isset($_COOKIE["account_number"])){
            $account_number = $_COOKIE['account_number'];
        } else {
            $account_number = '';

        }

$quote->setAccountNumber($account_number);$quote->save();
$logger->info(json_encode($quote->getData()));

        $order->setAccountNumber($quote->getAccountNumber());

        $logger->info("account number : ");
        $order->save();

        $logger->info("order saved");
    }
}
