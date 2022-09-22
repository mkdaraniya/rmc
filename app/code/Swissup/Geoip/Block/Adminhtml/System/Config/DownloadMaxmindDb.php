<?php

namespace Swissup\Geoip\Block\Adminhtml\System\Config;

class DownloadMaxmindDb extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Swissup\Geoip\Model\Downloader\Maxmind
     */
    private $downloader;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Swissup\Geoip\Model\Downloader\Maxmind $downloader
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Swissup\Geoip\Model\Downloader\Maxmind $downloader,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->downloader = $downloader;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $note = '';
        $updateDate = $this->downloader->getUpdateDate();
        if ($updateDate) {
            $note = '<div class="note">'
                . __('Last Update: %1', date('F j, Y', $updateDate))
                . '</div>';
        }

        $button = $this->getLayout()
            ->createBlock('Magento\Backend\Block\Widget\Button')
            ->setLabel($updateDate ? __('Update Database') : __('Download Database'))
            ->setTitle(__('Archive will be downloaded, unpacked, and *.mmdb database will be placed in "var/swissup/geoip" folder as "maxmind.mmdb" file.'))
            ->setAfterHtml($note)
            ->setDataAttribute([
                'mage-init' => [
                    'Swissup_Geoip/js/download' => [
                        'url' => $this->_urlBuilder->getUrl('swissup_geoip/maxmind/download'),
                        'licenseKeyField' => '#geoip_main_maxmind_service_license_key',
                        'editionField' => '#geoip_main_maxmind_database_edition',
                    ]
                ]
            ]);

        return $button->toHtml();
    }
}
