<?php

namespace Swissup\Geoip\Controller\Adminhtml\Maxmind;

use Magento\Framework\Controller\ResultFactory;

class Download extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Geoip::download_database';

    /**
     * @var \Swissup\Geoip\Model\Downloader\Maxmind
     */
    private $downloader;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Swissup\Geoip\Model\Downloader\Maxmind $downloader
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Swissup\Geoip\Model\Downloader\Maxmind $downloader
    ) {
        $this->downloader = $downloader;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response = new \Magento\Framework\DataObject();

        try {
            $this->downloader
                ->setLicense($this->getRequest()->getParam('license'))
                ->setEdition($this->getRequest()->getParam('edition'))
                ->download();

            $response->setMessage(__('Database successfully downloaded.'));
        } catch (\Exception $e) {
            $response->setMessage($e->getMessage());
            $response->setError(1);
        }

        return $resultJson->setData($response);
    }
}
