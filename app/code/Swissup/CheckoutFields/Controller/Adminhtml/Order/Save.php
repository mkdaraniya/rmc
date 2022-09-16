<?php
namespace Swissup\CheckoutFields\Controller\Adminhtml\Order;

class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Magento_Sales::actions_edit';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Swissup\CheckoutFields\Model\Field\Validator
     */
    protected $fieldsValidator;

    /**
     * @var \Swissup\CheckoutFields\Model\FieldFactory
     */
    protected $fieldFactory;

    /**
     * @var \Swissup\CheckoutFields\Model\Field\ValueFactory
     */
    protected $fieldValueFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Swissup\CheckoutFields\Model\Field\Validator $fieldsValidator
     * @param \Swissup\CheckoutFields\Model\FieldFactory $fieldFactory
     * @param \Swissup\CheckoutFields\Model\Field\ValueFactory $fieldValueFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Swissup\CheckoutFields\Model\Field\Validator $fieldsValidator,
        \Swissup\CheckoutFields\Model\FieldFactory $fieldFactory,
        \Swissup\CheckoutFields\Model\Field\ValueFactory $fieldValueFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fieldsValidator = $fieldsValidator;
        $this->fieldFactory = $fieldFactory;
        $this->fieldValueFactory = $fieldValueFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $storeId = $this->getRequest()->getParam('store_id');
        $fields = $this->getRequest()->getParam('swissup_checkout_field');

        if ($orderId && !empty($fields)) {
            if (!$this->fieldsValidator->isValid($fields)) {
                return $this->resultJsonFactory->create()->setData([
                    'error' => true,
                    'message' => __('Please fill all required fields.')
                ]);
            }

            foreach ($fields as $code => $value) {
                $fieldId = $this->fieldFactory->create()->loadByCode($code)->getId();
                $fieldValueModel = $this->fieldValueFactory->create()
                    ->loadByOrderFieldAndStore($orderId, $fieldId, $storeId);

                if (empty($value) && !is_numeric($value)) {
                    if ($fieldValueModel->getId()) {
                        $fieldValueModel->delete();
                    }

                    continue;
                }

                if (is_array($value)) {
                    $value = implode(',', $value);
                }

                $fieldValueModel
                    ->setFieldId($fieldId)
                    ->setStoreId($storeId)
                    ->setOrderId($orderId)
                    ->setValue($value)
                    ->save();
            }
        }

        return $this->resultJsonFactory->create()->setData([
            'success' => true
        ]);
    }
}
