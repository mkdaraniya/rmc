<?php

namespace Swissup\Orderattachment\Controller\Adminhtml\Attachment;

class Preview extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Swissup_Orderattachment::orderattachment_attachment';

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Swissup\Orderattachment\Helper\Attachment
     */
    protected $attachmentHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Swissup\Orderattachment\Helper\Attachment $attachmentHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Swissup\Orderattachment\Helper\Attachment $attachmentHelper
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->attachmentHelper = $attachmentHelper;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        return $this->attachmentHelper->previewAttachment(
            $this->getRequest(),
            $this->resultRawFactory->create()
        );
    }
}
