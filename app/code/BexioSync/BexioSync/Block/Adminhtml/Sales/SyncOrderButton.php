<?php

namespace BexioSync\BexioSync\Block\Adminhtml\Sales;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\AuthorizationInterface;

class SyncOrderButton implements ButtonProviderInterface
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * CustomButton constructor.
     *
     * @param AuthorizationInterface $authorization
     * @param Context $context
     */
    public function __construct(
        AuthorizationInterface $authorization
    ) {
        $this->authorization = $authorization;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Sync Order'),
            'on_click' => 'javascript:void(0)',
            'class' => '',
            'sort_order' => 10
        ];
    }
}
