<?php

namespace Swissup\CheckoutSuccess\Block\Adminhtml\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Swissup\CheckoutSuccess\Model\Config\Source\AvailableBlocks;

class Layout extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Path to template file.
     *
     * @var string
     */
    protected $_template = 'config-field/layout.phtml';

    /**
     * Available blocks for Success Page source model
     * @var \Swissup\CheckoutSuccess\Model\Config\Source\AvailableBlocks
     */
    protected $availableBlocks;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @param AvailableBlocks $availableBlocks
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        AvailableBlocks $availableBlocks,
        OrderCollectionFactory $orderCollectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->availableBlocks = $availableBlocks;
        $this->orderCollectionFactory = $orderCollectionFactory;
        return parent::__construct($context, $data);
    }

    /**
     * Render element HTML
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * Get options to initialize javascript
     *
     * @return string
     */
    public function getOptions()
    {
        return json_encode(
            [
                'disabled' => $this->getElement()->getData('disabled'),
                'parentId' => $this->getElement()->getContainer()->getHtmlId(),
                'availableBlocks' => $this->availableBlocks->toOptions(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = parent::render($element);
        // Do not render label. And make value take two cells.
        $labelHtml = '<td class="label"><label for="' .
            $element->getHtmlId() . '"><span' .
            $this->_renderScopeLabel($element) . '>' .
            $element->getLabel() .
            '</span></label></td>';

        $html = str_replace($labelHtml, '', $html, $count);

        return $html;
    }

    public function getStoreInConfig()
    {
        $form = $this->getForm();
        if ($form->getStoreCode()) {
            $store = $this->_storeManager->getStore($form->getStoreCode());
        } elseif ($form->getWebsiteCode()) {
            $store = $this->_storeManager->getWebsite($form->getWebsiteCode())
                ->getDefaultStore();
        } else {
            $store = $this->_storeManager->getDefaultStoreView();
        }

        return $store;
    }

    public function getLastOrder()
    {
        $store = $this->getStoreInConfig();
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter('store_id', $store->getId())
            ->setOrder('entity_id', 'DESC')
            ->setPageSize(1)
            ->setCurPage(1);
        return $collection->getFirstItem();
    }
}
