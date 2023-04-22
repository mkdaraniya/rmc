<?php
namespace BexioSync\BexioSync\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SyncOrderStatus extends Field
{
    protected $_template = 'BexioSync_BexioSync::system/config/orderstatus.phtml';

    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
    public function getAjaxUrl()
    {
        return $this->getUrl('bexiosync/system_config/orderstatus');
    }
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(
            [
                'id' => 'order_sync_button',
                'label' => __('Sync Order Status'),
            ]);
        return $button->toHtml();
    }
}