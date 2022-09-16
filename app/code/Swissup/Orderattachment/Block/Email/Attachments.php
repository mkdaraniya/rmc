<?php
namespace Swissup\Orderattachment\Block\Email;

class Attachments extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'email/order/attachments.phtml';

    /**
     * @var \Swissup\Orderattachment\Helper\Attachment
     */
    protected $attachmentHelper;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Swissup\Orderattachment\Helper\Attachment $attachmentHelper
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Swissup\Orderattachment\Helper\Attachment $attachmentHelper,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->attachmentHelper = $attachmentHelper;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if ($this->hasOrder()) {
            return $this->getData('order');
        }

        if ($this->hasOrderId()) {
            try {
                $order = $this->orderRepository->get($this->getData('order_id'));
                $this->setData('order', $order);
                return $order;
            } catch (\Exception $e) {
                //
            }
        }
    }

    public function getOrderId()
    {
        $order = $this->getOrder();
        if ($order) {
            return $order->getId();
        }
        return false;
    }

    public function getOrderAttachments()
    {
        $order = $this->getOrder();
        if (!$order) {
            return [];
        }
        $quoteId = $order->getQuoteId();

        return $this->attachmentHelper->getOrderAttachments($quoteId, false);
    }
}
