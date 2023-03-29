<?php
 
namespace BexioSync\BexioSync\Controller\Adminhtml\Index;
 
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
 
class massSyncStockPrice extends Action
{
 
    /**
     * @var Filter
     */
    protected $filter;
 
    /**
     * @var CollectionFactory
     */
    protected $prodCollFactory;

    protected $sourceItemFactory;
    protected $sourceItemsSaveInterface;

    
    protected $productResourceModel;
    protected $productFactory;
 
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
 
    /**
     * @param Context                                         $context
     * @param Filter                                          $filter
     * @param CollectionFactory                               $prodCollFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $prodCollFactory,
        \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSaveInterface,
        \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory,
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository)
    {
        $this->filter = $filter;
        $this->prodCollFactory = $prodCollFactory;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->productRepository = $productRepository;
        $this->productResourceModel = $productResourceModel;
        $this->productFactory = $productFactory;
        parent::__construct($context);
    }
 
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException | \Exception
     */
    public function execute()
    {
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager

        $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $storeRepositroy = $objectManager->get('\Magento\Store\Api\StoreRepositoryInterface');
        $stockModel = $objectManager->get('Magento\CatalogInventory\Model\Stock\ItemFactory')->create();
        $stockResource = $objectManager->get('Magento\CatalogInventory\Model\ResourceModel\Stock\Item');
        $stores = $storeRepositroy->getList();

        $sectionId = 'config';
        $groupId = 'settings';
        $fieldId = 'enable';

        $configPath = $sectionId.'/'.$groupId.'/'.$fieldId;
        $value =  $scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if(!$value){
            return true;
        }
        
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/bexiosync.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(__METHOD__);
        $logger->info("Product Sync stock & price Mass Action Start");

        try{
        $proResponse = [
            [
                "id" => 1,
                "title" => "iPhone 9",
                "description" => "An apple mobile which is nothing like apple",
                "price" => 200,
                "discountPercentage" => 12.96,
                "rating" => 4.69,
                "stock" => 94,
                "brand" => "Apple",
                "category" => "smartphones",
            ],
            [
                "id" => 2,
                "title" => "iPhone X",
                "description" =>
                "SIM-Free, Model A19211 6.5-inch Super Retina HD display with OLED technology A12 Bionic chip with ...",
                "price" => 300,
                "discountPercentage" => 17.94,
                "rating" => 4.44,
                "stock" => 34,
                "brand" => "Apple",
                "category" => "smartphones",
            ],
            [
                "id" => 3,
                "title" => "Samsung Universe 9",
                "description" =>
                "Samsung\'s new variant which goes beyond Galaxy to the Universe",
                "price" => 400,
                "discountPercentage" => 15.46,
                "rating" => 4.09,
                "stock" => 36,
                "brand" => "Samsung",
                "category" => "smartphones",
            ],
            [
                "id" => 4,
                "title" => "OPPOF19",
                "description" => "OPPO F19 is officially announced on April 2021.",
                "price" => 400,
                "discountPercentage" => 17.91,
                "rating" => 4.3,
                "stock" => 123,
                "brand" => "OPPO",
                "category" => "smartphones",
            ],
        ];


        $collection = $this->filter->getCollection($this->prodCollFactory->create());
        foreach ($collection->getAllIds() as $productId)
        {
            foreach($proResponse as $pro){
                $productDataObject = $this->productRepository->getById($productId);
                $productSku = $productDataObject->getSku();
                if($pro['title'] == $productSku){
                    // update stock data
                    $stockResource->load($stockModel, $productId,"product_id");
                    $stockModel->setQty($pro['stock']);
                    $stockResource->save($stockModel);

                    foreach($stores as $store){
                        $storeId = $store->getId();
                        // update price data
                        $productFactory = $this->productFactory->create();
                        $this->productResourceModel->load($productFactory, $productId);
                        $productFactory->setStoreId($storeId);
                        $productFactory->setPrice($pro['price']);
                        $this->productResourceModel->saveAttribute($productFactory, 'price');

                        $logger->info("Product ID : " . $productId);
                    }
                }
            }
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been modified.', $collection->getSize()));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

    }catch(\Exception $e){
        $logger->critical($e->getMessage());
        $logger->error($e);
        $this->messageManager->addErrorMessage(__("Something went to wrong, Please try again."));
    }

        return $resultRedirect->setPath('catalog/product/index');
    }
}