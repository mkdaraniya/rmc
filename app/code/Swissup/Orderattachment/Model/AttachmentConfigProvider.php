<?php
namespace Swissup\Orderattachment\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Swissup\Orderattachment\Model\Attachment;

class AttachmentConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Swissup\Orderattachment\Model\ResourceModel\Attachment\Collection
     */
    protected $attachmentCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $urlBuilder
     * @param CheckoutSession $checkoutSession
     * @param \Swissup\Orderattachment\Model\ResourceModel\Attachment\Collection $attachmentCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder,
        CheckoutSession $checkoutSession,
        \Swissup\Orderattachment\Model\ResourceModel\Attachment\Collection $attachmentCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Session $customerSession
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->checkoutSession = $checkoutSession;
        $this->attachmentCollection = $attachmentCollection;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
    }

    public function getConfig()
    {
        $attachSize = $this->getOrderAttachmentFileSize();
        return [
            'swissupAttachmentEnabled' => $this->isOrderAttachmentEnabled(),
            'attachments' => $this->getUploadedAttachments(),
            'swissupAttachmentLimit'    => $this->getOrderAttachmentFileLimit(),
            'swissupAttachmentSize'     => $this->getOrderAttachmentFileSize(),
            'swissupAttachmentExt'      => $this->getOrderAttachmentFileExt(),
            'swissupAttachmentUpload'   => $this->getAttachmentUploadUrl(),
            'swissupAttachmentUpdate'   => $this->getAttachmentUpdateUrl(),
            'swissupAttachmentRemove'   => $this->getAttachmentRemoveUrl(),
            'removeItem' => __('Remove Item'),
            'swissupAttachmentInvalidExt' => __('Invalid File Type'),
            'swissupAttachmentComment' => __('Write comment here'),
            'swissupAttachmentInvalidSize' => __('Size of the file is greather than allowed') . '(' . $attachSize . ' KB)',
            'swissupAttachmentInvalidLimit' => __('You have reached the limit of files'),
        ];
    }

    private function getUploadedAttachments()
    {
        if ($quoteId = $this->checkoutSession->getQuote()->getId()) {
            $attachments = $this->attachmentCollection
                ->addFieldToFilter('main_table.quote_id', $quoteId)
                ->addFieldToFilter('main_table.order_id', ['is' => new \Zend_Db_Expr('null')]);

            $defaultStoreId = $this->storeManager->getDefaultStoreView()->getStoreId();
            foreach ($attachments as $attachment) {
                $url = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                    . "orderattachment/"
                    . $attachment['path'];
                $attachment->setUrl($url);
                $preview = $this->storeManager->getStore($defaultStoreId)->getUrl(
                    'orderattachment/attachment/preview',
                    [
                        'attachment' => $attachment['attachment_id'],
                        'hash' => $attachment['hash']
                    ]
                );
                $attachment->setPreview($preview);
                $attachment->setPath(basename($attachment->getPath()));
            }
            $result = $attachments->toArray();
            $result = $result['items'];
            return $result;
        }

        return false;
    }

    private function isOrderAttachmentEnabled()
    {
        $moduleEnabled = $this->scopeConfig->getValue(
            Attachment::XML_PATH_ENABLE_ATTACHMENT,
            ScopeInterface::SCOPE_STORE
        );
        $onCheckout = $this->scopeConfig->getValue(
            Attachment::XML_PATH_ATTACHMENT_ON_CHECKOUT,
            ScopeInterface::SCOPE_STORE
        );

        // restrict order attachments for certain customer groups
        $groupIds = (string) $this->scopeConfig->getValue(
            Attachment::XML_PATH_ATTACHMENT_CUSTOMER_GROUP,
            ScopeInterface::SCOPE_STORE
        );

        $groupIds = array_filter(explode(',', $groupIds));

        if ($groupIds) {
            $getCurrentCustomerGroup = $this->customerSession->getCustomer()->getGroupId();
            if (!in_array($getCurrentCustomerGroup, $groupIds)) {
                return false;
            }
        }

        return ($moduleEnabled && $onCheckout);
    }

    private function getOrderAttachmentFileLimit()
    {
        return $this->scopeConfig->getValue(
            Attachment::XML_PATH_ATTACHMENT_FILE_LIMIT,
            ScopeInterface::SCOPE_STORE
        );
    }

    private function getOrderAttachmentFileSize()
    {
        return $this->scopeConfig->getValue(
            Attachment::XML_PATH_ATTACHMENT_FILE_SIZE,
            ScopeInterface::SCOPE_STORE
        );
    }

    private function getOrderAttachmentFileExt()
    {
        return $this->scopeConfig->getValue(
            Attachment::XML_PATH_ATTACHMENT_FILE_EXT,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getAttachmentUploadUrl()
    {
        return $this->urlBuilder->getUrl('orderattachment/attachment/upload');
    }

    public function getAttachmentUpdateUrl()
    {
        return $this->urlBuilder->getUrl('orderattachment/attachment/update');
    }

    public function getAttachmentRemoveUrl()
    {
        return $this->urlBuilder->getUrl('orderattachment/attachment/delete');
    }
}
