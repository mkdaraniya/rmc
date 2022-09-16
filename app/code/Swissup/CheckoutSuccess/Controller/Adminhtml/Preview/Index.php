<?php

namespace Swissup\CheckoutSuccess\Controller\Adminhtml\Preview;

use Magento\Backend\App\Action\Context;
use Magento\Config\Model\Config\Factory as ConfigFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Math\Random;
use Magento\Store\Model\StoreManagerInterface;

class Index extends \Magento\Backend\App\Action
{
    // const ADMIN_RESOURCE = 'Swissup_CheckoutSuccess::builder_index';

    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ConfigFactory         $configFactory
     * @param Random                $mathRandom
     * @param StoreManagerInterface $storeManager
     * @param Context               $context
     */
    public function __construct(
        ConfigFactory $configFactory,
        Random $mathRandom,
        StoreManagerInterface $storeManager,
        Context $context
    ) {
        $this->mathRandom = $mathRandom;
        $this->configFactory = $configFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $hash = $this->mathRandom->getUniqueHash();
        $configData = $this->prepareConfigData($hash);
        $configModel = $this->configFactory->create(['data' => $configData]);
        $configModel->save();
        $this->_eventManager->dispatch(
            'admin_system_config_save',
            [
                'configData' => $configData,
                'request' => $this->getRequest()
            ]
        );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setUrl(
            $this->getRedirectUrl($hash)
        );
    }

    private function prepareConfigData(string $hash): array
    {
        $request = $this->getRequest();
        $currentTime = time();

        return [
            'section' => $request->getParam('section'),
            'website' => $request->getParam('website'),
            'store' => $request->getParam('store'),
            'groups' => [
                'layout' => [
                    'fields' => [
                        'preview_hash' => [
                            'value' => $hash
                        ],
                        'preview_expires' => [
                            'value' => $currentTime + 10 * 60
                        ]
                    ]
                ]
            ]
        ];
    }

    private function getRedirectUrl(string $hash): string
    {
        $request = $this->getRequest();
        $store = $this->storeManager->getStore($request->getParam('store'));
        $query = [
            'hash' => $hash,
            'previewObjectId' => $request->getParam('previewObjectId'),
            'builder' => $request->getParam('builder'),
            '___store' => $store->getCode()
        ];

        return $store->getBaseUrl()
            . 'checkout/onepage/success/?'
            . http_build_query(array_filter($query));
    }
}
