<?php

namespace Swissup\CheckoutSuccess\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Swissup\CheckoutSuccess\Model\Config\Source\AvailableBlocks as AllowedBlocks;

class Builder extends AbstractHelper
{
    /**
     * @var AllowedBlocks
     */
    private $allowedBlocks;

    /**
     * Constructor
     *
     * @param Context $context
     */
    public function __construct(
        AllowedBlocks $allowedBlocks,
        Context $context
    ) {
        $this->allowedBlocks = $allowedBlocks;
        parent::__construct($context);
    }

    public function getAllowedBlocks()
    {
        $items = $this->allowedBlocks->toOptions();

        return array_keys($items);
    }

    public function getBlockLabel($name): string
    {
        $items = $this->allowedBlocks->toOptions();
        $item = $items[$name] ?? false;

        if (is_scalar($item)) {
            return (string)$item;
        } elseif (is_array($item)) {
            return $item['label'] ?? '';
        }

        return '';
    }

    public function getBlockDeclaration($name): mixed
    {
        $items = $this->allowedBlocks->toOptions();

        return $items[$name] ?? false;
    }
}
