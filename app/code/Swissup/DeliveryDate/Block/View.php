<?php

namespace Swissup\DeliveryDate\Block;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Swissup\DeliveryDate\Model\DeliverydateFactory
     */
    protected $deliverydateFactory;

    /**
     * @var \Swissup\DeliveryDate\Model\Deliverydate
     */
    protected $deliverydateModel;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Swissup\DeliveryDate\Model\DeliverydateFactory $deliverydateFactory
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Swissup\DeliveryDate\Model\DeliverydateFactory $deliverydateFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->deliverydateFactory = $deliverydateFactory;
        $this->orderRepository = $orderRepository;
        $this->localeResolver = $localeResolver;
        parent::__construct($context, $data);
    }

    /**
     * @return \Swissup\DeliveryDate\Model\Deliverydate
     */
    private function getDeliveryDateModel()
    {
        if (!$this->deliverydateModel) {
            if ($this->getOrder()) {
                $this->deliverydateModel = $this->getOrder()
                    ->getExtensionAttributes()
                    ->getSwissupDeliveryDate();
            }

            if (!$this->deliverydateModel) {
                $this->deliverydateModel = $this->deliverydateFactory->create();
                if ($this->getOrder()) {
                    $this->deliverydateModel->loadByOrderId($this->getOrder()->getId());
                }
            }
        }
        return $this->deliverydateModel;
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
                $order = $this->orderRepository->get($this->getOrderId());
                $this->setData('order', $order);
                return $order;
            } catch (\Exception $e) {
                //
            }
        }

        if ($this->coreRegistry->registry('current_order')) {
            return $this->coreRegistry->registry('current_order');
        }
        if ($this->coreRegistry->registry('order')) {
            return $this->coreRegistry->registry('order');
        }
    }

    /**
     * @return string
     */
    public function getDeliveryDate($format = \IntlDateFormatter::SHORT)
    {
        $date = '';
        $deliveryDate = $this->getDeliveryDateModel();
        if ($deliveryDate->getId()) {
            $date = $deliveryDate->getDate();
            $formatter = new \IntlDateFormatter(
                $this->localeResolver->getLocale(),
                $format,
                \IntlDateFormatter::NONE
            );
            $date = $formatter->format(new \DateTime($date));
        }

        return $date;
    }

    /**
     * @return string
     */
    public function getDeliveryTime()
    {
        $time = '';
        $deliveryDate = $this->getDeliveryDateModel();
        if ($deliveryDate->getId()) {
            $time = $deliveryDate->getTimerange();
        }
        return $time;
    }
}
