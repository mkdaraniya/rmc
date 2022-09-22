<?php

namespace Swissup\CheckoutSuccess\Controller\Adminhtml\Config;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Swissup\CheckoutSuccess\Helper\Builder;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var Builder
     */
    private $builder;

    /**
     * @param Builder $builder
     * @param Context $context
     */
    public function __construct(
        Builder $builder,
        Context $context
    ) {
        $this->builder = $builder;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $request = $this->getRequest();
        $block = $request->getParam('block');
        $declaration = $this->builder->getBlockDeclaration($block);
        $data = $declaration['config']['frontendModel'] ?? [];
        $type = $data['type'] ?? 'Magento\Framework\View\Element\Template';

        $layout = $this->_view->getLayout();
        $layoutBlock = $layout->createBlock($type, '', $data);

        return $this->resultFactory
            ->create(ResultFactory::TYPE_RAW)
            ->setContents($layoutBlock->toHtml());
    }
}
