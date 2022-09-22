<?php
namespace Swissup\CheckoutFields\Block\Adminhtml\Order\View;

use Swissup\CheckoutFields\Model\ResourceModel\Field\CollectionFactory as FieldCollectionFactory;
use Swissup\CheckoutFields\Model\ResourceModel\Field\Value\CollectionFactory as ValueCollectionFactory;

class Fields extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /**
     * Checkout fields collection factory
     * @var FieldCollectionFactory
     */
    protected $fieldsCollectionFactory;

    /**
     * Field values collection factory
     * @var ValueCollectionFactory
     */
    public $fieldValueCollectionFactory;

    /**
     * @var \Swissup\CheckoutFields\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param FieldCollectionFactory $fieldsCollectionFactory
     * @param ValueCollectionFactory $fieldValueCollectionFactory
     * @param \Swissup\CheckoutFields\Helper\Data $helper
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        FieldCollectionFactory $fieldsCollectionFactory,
        ValueCollectionFactory $fieldValueCollectionFactory,
        \Swissup\CheckoutFields\Helper\Data $helper,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        array $data = []
    ) {
        $this->fieldsCollectionFactory = $fieldsCollectionFactory;
        $this->fieldValueCollectionFactory = $fieldValueCollectionFactory;
        $this->helper = $helper;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if ($this->hasOrderId()) {
            try {
                $order = $this->orderRepository->get($this->getOrderId());
                $this->setData('order', $order);
                return $order;
            } catch (\Exception $e) {
                //
            }
        }

        try {
            return parent::getOrder();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get checkout fields edit link
     *
     * @return string
     */
    public function getEditLink()
    {
        if ($this->_authorization->isAllowed('Magento_Sales::actions_edit')) {
            return '<a id="checkout-fields-edit-link" href="#">' . __('Edit') . '</a>';
        }

        return '';
    }

    /**
     * Get Fields Values for order
     * @param  \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Swissup\CheckoutFields\Api\Data\FieldDataInterface[]|null
     */
    public function getFields($order = null)
    {
        if (!$order) {
            $order = $this->getOrder();
        }

        if ($selectedFields = $this->getData('fields_to_show')) {
            $selectedFields = explode(',', $selectedFields);
        }

        return $this->helper->getOrderFieldsValues($order, $selectedFields);
    }

    /**
     * Retrieve serialized JS layout configuration ready to use in template
     *
     * @return string
     */
    public function getJsLayout()
    {
        $this->prepareJsLayout();

        return parent::getJsLayout();
    }

    private function prepareJsLayout()
    {
        if (!$this->helper->isEnabled()) {
            return null;
        }

        $config = [];
        $dataConfig = [];
        $order = $this->getOrder();
        $storeId = $order->getStore()->getId();
        $fields = $this->fieldsCollectionFactory->create()
            ->addStoreFilter($storeId)
            ->addIsActiveFilter(1)
            ->addOrder(
                \Swissup\CheckoutFields\Api\Data\FieldInterface::SORT_ORDER,
                \Magento\Framework\Data\Collection::SORT_ORDER_ASC
            );

        $fieldValues = $this->fieldValueCollectionFactory->create()
            ->addOrderFilter($order->getId())
            ->addStoreFilter($storeId)
            ->load();

        foreach ($fields as $field) {
            $label = $field->getStoreLabel($storeId);

            $validation = [];
            if ($field->getIsRequired() == 1) {
                if ($field->getFrontendInput() == 'multiselect') {
                    $validation['validate-one-required'] = true;
                }
                $validation['required-entry'] = true;
            }

            $options = $this->helper->getFieldOptions($field, $storeId);
            $default = $this->helper->getDefaultValue($field);

            $config[$field->getAttributeCode()] = $this->helper
                ->getFieldComponent($field, $label, $validation, $default, $options, 'swissupCheckoutFields');

            // set fields values
            $fieldValueItem = $fieldValues->getItemByColumnValue('field_id', $field->getId());
            $fieldValue = $fieldValueItem ? $fieldValueItem->getValue() : '';
            if ($field->getFrontendInput() == 'multiselect') {
                $fieldValue = $fieldValue ? explode(',', $fieldValue) : [];
            }

            $dataConfig['swissup_checkout_field[' . $field->getAttributeCode() . ']'] = $fieldValue;
        }

        // save button
        $config['checkoutfields-save-button'] = [
            'title' => __('Save'),
            'visible' => true,
            'sortOrder' => 9998,
            'component' => 'Magento_Ui/js/form/components/button',
            'url' => $this->getUrl(
                'checkoutfields/order/save',
                ['order_id' => $this->getOrder()->getId(), 'store_id' => $storeId]
            )
        ];

        // cancel button
        $config['checkoutfields-cancel-button'] = [
            'title' => __('Cancel'),
            'visible' => true,
            'sortOrder' => 9999,
            'component' => 'Magento_Ui/js/form/components/button'
        ];

        $this->jsLayout['components']['swissup-checkout-fields']['children'] = $config;
        $this->jsLayout['components']['swissupCheckoutFields']['swissupCheckoutFields'] = $dataConfig;
    }
}
