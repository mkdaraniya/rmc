<?php
namespace Swissup\CheckoutFields\Controller\Adminhtml\Field;

use Magento\Framework\DataObject;

class Validate extends \Magento\Catalog\Controller\Adminhtml\Product\Attribute
{
    const DEFAULT_MESSAGE_KEY = 'message';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var array
     */
    private $multipleAttributeList;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Cache\FrontendInterface $attributeLabelCache
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param array $multipleAttributeList
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Cache\FrontendInterface $attributeLabelCache,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        array $multipleAttributeList = []
    ) {
        parent::__construct($context, $attributeLabelCache, $coreRegistry, $resultPageFactory);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->multipleAttributeList = $multipleAttributeList;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $response = new DataObject();
        $response->setError(false);

        $attributeCode = $this->getRequest()->getParam('attribute_code');
        $frontendLabel = $this->getRequest()->getParam('frontend_label');
        $attributeCode = $attributeCode ?: $this->generateCode($frontendLabel[0]);
        $attributeId = $this->getRequest()->getParam('field_id');
        $field = $this->_objectManager->create(
            'Swissup\CheckoutFields\Model\Field'
        )->loadByCode(
            $attributeCode
        );

        if ($field->getId() && !$attributeId) {
            $message = strlen($this->getRequest()->getParam('attribute_code'))
                ? __('An field with this code already exists.')
                : __('An field with the same code (%1) already exists.', $attributeCode);

            $this->setMessageToResponse($response, [$message]);

            $response->setError(true);
            $response->setProductAttribute($field->toArray());
        }

        $multipleOption = $this->getRequest()->getParam('frontend_input');
        $multipleOption = null === $multipleOption ? 'select' : $multipleOption;
        if (isset($this->multipleAttributeList[$multipleOption]) && null !== $multipleOption) {
            $this->checkUniqueOption(
                $response,
                $this->getRequest()->getParam($this->multipleAttributeList[$multipleOption])
            );
        }

        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }

    /**
     * Throws Exception if not unique values into options
     * @param array $optionsValues
     * @param array $deletedOptions
     * @return bool
     */
    private function isUniqueAdminValues(array $optionsValues, array $deletedOptions)
    {
        $adminValues = [];
        foreach ($optionsValues as $optionKey => $values) {
            if (!(isset($deletedOptions[$optionKey]) and $deletedOptions[$optionKey] === '1')) {
                $adminValues[] = reset($values);
            }
        }
        $uniqueValues = array_unique($adminValues);
        return ($uniqueValues === $adminValues);
    }

    /**
     * Set message to response object
     *
     * @param DataObject $response
     * @param string[] $messages
     * @return DataObject
     */
    private function setMessageToResponse($response, $messages)
    {
        $messageKey = $this->getRequest()->getParam('message_key', static::DEFAULT_MESSAGE_KEY);
        if ($messageKey === static::DEFAULT_MESSAGE_KEY) {
            $messages = reset($messages);
        }
        return $response->setData($messageKey, $messages);
    }

    /**
     * @param DataObject $response
     * @param array|null $options
     */
    private function checkUniqueOption(DataObject $response, array $options = null)
    {
        if (is_array($options) && !$this->isUniqueAdminValues($options['value'], $options['delete'])) {
            $this->setMessageToResponse($response, [__('The value of Admin must be unique.')]);
            $response->setError(true);
        }
    }
}
