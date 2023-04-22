<?php
namespace BexioSync\BexioSync\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Orderstatus extends Action
{

    protected $resultJsonFactory;

    /**
     * @var Data
     */

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Order relations data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            //$this->_getSyncSingleton()->orderRelations();

            // run order sync cron job
            $cron = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('BexioSync\BexioSync\Cron\OrderStatusCron');

            $cron->execute();

        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();

        return $result->setData(['success' => true]);
    }


    protected function _getSyncSingleton()
    {
        return $this->_objectManager->get('BexioSync\BexioSync\Model\Relation');
    }

}
?>