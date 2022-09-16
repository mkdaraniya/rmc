<?php
namespace Swissup\CheckoutFields\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderSaveAfter implements ObserverInterface
{
    /**
     * @var \Swissup\CheckoutFields\Model\ResourceModel\Field\Value\CollectionFactory
     */
    protected $fieldValuesCollectionFactory;

    /**
     * @var \Swissup\CheckoutFields\Model\FieldFactory
     */
    protected $fieldFactory;

    /**
     * Store Manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Swissup\CheckoutFields\Model\ResourceModel\Field\Value\CollectionFactory $fieldValuesCollectionFactory
     * @param \Swissup\CheckoutFields\Model\FieldFactory $fieldFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Swissup\CheckoutFields\Model\ResourceModel\Field\Value\CollectionFactory $fieldValuesCollectionFactory,
        \Swissup\CheckoutFields\Model\FieldFactory $fieldFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->fieldValuesCollectionFactory = $fieldValuesCollectionFactory;
        $this->fieldFactory = $fieldFactory;
        $this->storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $storeId = $this->storeManager->getStore()->getId();

        $fieldsCollection = $this->fieldValuesCollectionFactory
            ->create()
            ->addStoreFilter($storeId)
            ->addQuoteFilter($order->getQuoteId())
            ->load();

        foreach ($fieldsCollection as $fieldValue) {
            $fieldValue->setOrderId($order->getId())->save();
        }

        return $this;
    }
}
