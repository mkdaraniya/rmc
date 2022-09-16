<?php
namespace Swissup\CheckoutFields\Controller\Adminhtml\Field;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Magento\Catalog\Controller\Adminhtml\Product\Attribute
{
    /**
     * Admin resource
     */
    const ADMIN_RESOURCE = 'Swissup_CheckoutFields::checkoutfields';

    /**
     * @var \Swissup\CheckoutFields\Model\FieldFactory
     */
    protected $fieldFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Cache\FrontendInterface $attributeLabelCache
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Swissup\CheckoutFields\Model\FieldFactory $fieldFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Cache\FrontendInterface $attributeLabelCache,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Swissup\CheckoutFields\Model\FieldFactory $fieldFactory
    ) {
        $this->fieldFactory = $fieldFactory;
        parent::__construct($context, $attributeLabelCache, $coreRegistry, $resultPageFactory);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $this->preprocessOptionsData($data);
            $fieldId = $this->getRequest()->getParam('field_id');
            $attributeCode = $this->getRequest()->getParam('attribute_code')
                ?: $this->generateCode($this->getRequest()->getParam('frontend_label')[0]);
            if (strlen($attributeCode) > 0) {
                $validatorAttrCode = new \Zend_Validate_Regex(['pattern' => '/^[a-z][a-z_0-9]{0,30}$/']);
                if (!$validatorAttrCode->isValid($attributeCode)) {
                    $this->messageManager->addError(
                        __(
                            'Attribute code "%1" is invalid. Please use only letters (a-z), ' .
                            'numbers (0-9) or underscore(_) in this field, first character should be a letter.',
                            $attributeCode
                        )
                    );
                    return $this->returnResult(
                        '*/*/edit',
                        ['field_id' => $fieldId, '_current' => true]
                    );
                }
            }
            $data['attribute_code'] = $attributeCode;

            /* @var $model \Swissup\CheckoutFields\Model\Field */
            $model = $this->fieldFactory->create();

            if ($fieldId) {
                $model->load($fieldId);
                if (!$model->getId()) {
                    $this->messageManager->addError(__('This field no longer exists.'));
                    return $this->returnResult('*/*/');
                }

                $data['attribute_code'] = $model->getAttributeCode();
                $data['frontend_input'] = $model->getFrontendInput();
            }

            $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);
            if ($defaultValueField) {
                $data['default_value'] = $this->getRequest()->getParam($defaultValueField);
            }

            $model->addData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved the checkout field.'));

                $this->_session->setFieldData(false);
                if ($this->getRequest()->getParam('back', false)) {
                    return $this->returnResult(
                        '*/*/edit',
                        ['field_id' => $model->getId(), '_current' => true]
                    );
                }
                return $this->returnResult('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_session->setFieldData($data);
                return $this->returnResult(
                    '*/*/edit',
                    ['field_id' => $fieldId, '_current' => true]
                );
            }
        }
        return $this->returnResult('*/*/');
    }

    /**
     * @param string $path
     * @param array $params
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    private function returnResult($path = '', array $params = [])
    {
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
            ->setPath($path, $params);
    }

    /**
     * @param array $data
     * @return void
     */
    private function preprocessOptionsData(&$data)
    {
        if (isset($data['serialized_options'])) {
            $serializedOptions = json_decode($data['serialized_options'], JSON_OBJECT_AS_ARRAY);
            foreach ($serializedOptions as $serializedOption) {
                $option = [];
                parse_str($serializedOption, $option);
                $data = array_replace_recursive($data, $option);
            }
        }
        unset($data['serialized_options']);
    }
}
