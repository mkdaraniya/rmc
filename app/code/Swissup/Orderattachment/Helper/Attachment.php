<?php
namespace Swissup\Orderattachment\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Attachment extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Swissup\Orderattachment\Model\Upload
     */
    protected $uploadModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $random;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Swissup\Orderattachment\Model\AttachmentFactory
     */
    protected $attachmentFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Swissup\Orderattachment\Model\Upload $uploadModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Math\Random $random
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Swissup\Orderattachment\Model\AttachmentFactory $attachmentFactory
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Swissup\Orderattachment\Model\Upload $uploadModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Math\Random $random,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Swissup\Orderattachment\Model\AttachmentFactory $attachmentFactory,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder
    ) {
        parent::__construct($context);
        $this->uploadModel = $uploadModel;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->random = $random;
        $this->dateTime = $dateTime;
        $this->attachmentFactory = $attachmentFactory;
        $this->fileSystem = $fileSystem;
        $this->escaper = $escaper;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Upload file and save attachment
     * @param \Magento\Framework\App\Request\Http $request
     * @return array
     */
    public function saveAttachment($request)
    {
        try {
            $uploadData = $request->getFiles()->get('order-attachment')[0];
            $result = $this->uploadModel->uploadFileAndGetInfo($uploadData);

            unset($result['tmp_name']);
            unset($result['path']);
            $result['success'] = true;
            $result['url'] = $this->storeManager->getStore()
                ->getBaseUrl() . "var/orderattachment/" . $result['file'];

            $hash = $this->random->getRandomString(32);
            $date = $this->dateTime->gmtDate('Y-m-d H:i:s');

            $attachment = $this->attachmentFactory
                ->create()
                ->setPath($result['file'])
                ->setHash($hash)
                ->setComment('')
                ->setType($result['type'])
                ->setUploadedAt($date)
                ->setModifiedAt($date);

            if ($orderId = $request->getParam('order_id')) {
                $attachment->setOrderId($orderId);
            } else {
                $quote = $this->checkoutSession->getQuote();
                $attachment->setQuoteId($quote->getId());
            }

            $attachment->save();

            $preview = $this->_getUrl(
                'orderattachment/attachment/preview',
                [
                    'attachment' => $attachment->getId(),
                    'hash' => $attachment->getHash()
                ]
            );
            $download = $this->_getUrl(
                'orderattachment/attachment/preview',
                [
                    'attachment' => $attachment->getId(),
                    'hash' => $attachment->getHash(),
                    'download' => 1
                ]
            );
            $result['preview'] = $preview;
            $result['download'] = $download;
            $result['attachment_id'] = $attachment->getId();
            $result['hash'] = $attachment->getHash();
            $result['comment'] = '';
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'error' => __("We can't upload the file right now."),
                'errorcode' => $e->getCode()
            ];
        }

        return $result;
    }

    /**
     * Delete order attachment
     * @param \Magento\Framework\App\Request\Http $request
     * @return array
     */
    public function deleteAttachment($request)
    {
        $result = [];
        $isAjax = $request->isAjax();
        $isPost = $request->isPost();
        $requestParams = $request->getParams();
        $attachmentId = $requestParams['attachment'] ?? null;
        $hash = $requestParams['hash'] ?? null;
        $orderId = $requestParams['order_id'] ?? null;

        if (!$isAjax || !$isPost || !$attachmentId || !$hash) {
            return ['success' => false, 'error' => __('Invalid Request Params')];
        }

        try {
            $attachment = $this->attachmentFactory->create()->load($attachmentId);

            if (!$attachment->getId() || ($orderId && $orderId !== $attachment->getOrderId())) {
                return ['success' => false, 'error' => __('Can\'t find a attachment to delete.')];
            }

            if ($hash !== $attachment->getHash()) {
                return ['success' => false, 'error' => __('Invalid Hash Params')];
            }

            $varDirectory = $this->fileSystem
                ->getDirectoryRead(DirectoryList::VAR_DIR)
                ->getAbsolutePath("orderattachment");

            $attachFile = $varDirectory . "/" . $attachment->getPath();
            if (file_exists($attachFile)) {
                unlink($attachFile);
            }
            $attachment->delete();

            $result = ['success' => true];
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }

        return $result;
    }

    /**
     * Save attachment comment
     * @param \Magento\Framework\App\Request\Http $request
     * @return array
     */
    public function updateAttachment($request)
    {
        $result = [];
        $isAjax = $request->isAjax();
        $isPost = $request->isPost();
        $requestParams = $request->getParams();
        $attachmentId = $requestParams['attachment'] ?? null;
        $hash = $requestParams['hash'] ?? null;

        if (!$isAjax || !$isPost || !$attachmentId || !$hash) {
            return ['success' => false, 'error' => __('Invalid Request Params')];
        }

        $comment = $this->escaper->escapeHtml($requestParams['comment']);
        $orderId = $requestParams['order_id'] ?? null;

        try {
            $attachment = $this->attachmentFactory->create()->load($attachmentId);

            if (!$attachment->getId() || ($orderId && $orderId !== $attachment->getOrderId())) {
                return ['success' => false, 'error' => __('Can\'t find a attachment to update.')];
            }

            if ($hash !== $attachment->getHash()) {
                return ['success' => false, 'error' => __('Invalid Hash Params')];
            }

            $attachment->setComment($comment);
            $attachment->save();
            $result = ['success' => true];
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }

        return $result;
    }

    /**
     * Preview attachment
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Controller\Result\Raw $response
     */
    public function previewAttachment($request, $response)
    {
        $result = [];
        $attachmentId = $request->getParam('attachment');
        $hash = $request->getParam('hash');
        $download = $request->getParam('download');

        if (!$attachmentId || !$hash) {
            $result = ['success' => false, 'error' => __('Invalid Request Params')];
            $response->setHeader('Content-type', 'text/plain')
                ->setContents(json_encode($result));

            return $response;
        }

        try {
            $attachment = $this->attachmentFactory->create()->load($attachmentId);

            if (!$attachment->getId()) {
                $result = ['success' => false, 'error' => __('Can\'t find a attachment to preview.')];
                $response->setHeader('Content-type', 'text/plain')
                    ->setContents(json_encode($result));

                return $response;
            }

            if ($hash !== $attachment->getHash()) {
                $result = ['success' => false, 'error' => __('Invalid Hash Params')];
                $response->setHeader('Content-type', 'text/plain')
                    ->setContents(json_encode($result));

                return $response;
            }

            $varDirectory = $this->fileSystem
                ->getDirectoryRead(DirectoryList::VAR_DIR)
                ->getAbsolutePath("orderattachment");
            $attachmentFile = $varDirectory . "/" . $attachment->getPath();

            $attachmentType = explode('/', $attachment->getType());
            $handle = fopen($attachmentFile, "r");
            if ($download) {
                $response
                    ->setHeader('Content-Type', 'application/octet-stream', true)
                    // disable cache to fix serialize error in FPC
                    // @see \Magento\Framework\App\PageCache\Kernel::process
                    ->setHeader('Cache-Control', 'public, must-revalidate, max-age=0')
                    ->setHeader(
                        'Content-Disposition',
                        'attachment; filename="' . basename($attachmentFile) . '"',
                        true
                    );
            } else {
                $response->setHeader('Content-Type', $attachment->getType(), true);
            }
            $response->setContents(fread($handle, filesize($attachmentFile)));
            fclose($handle);
        } catch (\Exception $e) {
            $result = ['success' => false, 'error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            $response->setHeader('Content-type', 'text/plain');
            $response->setContents(json_encode($result));
        }

        return $response;
    }

    /**
     * Get attachment config json
     * @param mixed $block
     * @return string
     */
    public function getAttachmentConfig($block)
    {
        $config = [
            'attachments' => $block->getOrderAttachments(),
            'limit' => $this->scopeConfig->getValue(
                \Swissup\Orderattachment\Model\Attachment::XML_PATH_ATTACHMENT_FILE_LIMIT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            'size' => $this->scopeConfig->getValue(
                \Swissup\Orderattachment\Model\Attachment::XML_PATH_ATTACHMENT_FILE_SIZE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            'ext' => $this->scopeConfig->getValue(
                \Swissup\Orderattachment\Model\Attachment::XML_PATH_ATTACHMENT_FILE_EXT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            'uploadUrl' => $block->getUploadUrl(),
            'updateUrl' => $block->getUpdateUrl(),
            'removeUrl' => $block->getRemoveUrl()
        ];

        return $this->jsonEncoder->encode($config);
    }

    /**
     * Load order attachments by order id or by quote id
     * @param  int $entityId
     * @param  bool $byOrder load by order or by quote
     * @return array
     */
    public function getOrderAttachments($entityId, $byOrder = true)
    {
        $attachmentModel = $this->attachmentFactory->create();
        if ($byOrder) {
            $attachments = $attachmentModel->getOrderAttachments($entityId);
            $baseUrl = $this->storeManager->getStore()
                ->getBaseUrl() . DirectoryList::VAR_DIR . '/orderattachment/';
        } else {
            $attachments = $attachmentModel->getAttachmentsByQuote($entityId);
        }

        if (count($attachments) > 0) {
            foreach ($attachments as &$attachment) {
                $download = $this->_urlBuilder->getUrl(
                    'orderattachment/attachment/preview',
                    [
                        'attachment' => $attachment['attachment_id'],
                        'hash' => $attachment['hash'],
                        'download' => 1
                    ]
                );
                $attachment['path'] = basename($attachment['path']);
                $attachment['download'] = $download;
                $attachment['comment'] = $this->escaper->escapeHtml($attachment['comment']);

                if ($byOrder) {
                    $preview = $this->_urlBuilder->getUrl(
                        'orderattachment/attachment/preview',
                        [
                            'attachment' => $attachment['attachment_id'],
                            'hash' => $attachment['hash']
                        ]
                    );
                    $attachment['preview'] = $preview;
                    $attachment['url'] = $baseUrl . $attachment['path'];
                }
            }

            return $attachments;
        }

        return false;
    }

    /**
     * Get config for order attachments enabled
     * @return boolean
     */
    public function isOrderAttachmentEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            \Swissup\Orderattachment\Model\Attachment::XML_PATH_ENABLE_ATTACHMENT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get config for order view file upload enabled
     * @return boolean
     */
    public function isAllowedFileUpload()
    {
        return (bool)$this->scopeConfig->getValue(
            \Swissup\Orderattachment\Model\Attachment::XML_PATH_ATTACHMENT_ON_ORDER_VIEW,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
