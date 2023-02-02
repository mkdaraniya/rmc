<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MultiStoreViewPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricing\Plugin\SalesRule\Model\Rule\Action\Discount;

use Magento\SalesRule\Model\Rule\Action\Discount\ByFixed;
use Magento\SalesRule\Model\Rule\Action\Discount\CartFixed;
use Magento\SalesRule\Model\Rule\Action\Discount\ToFixed;

class Fixed
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Convert discount amount to store currency.
     *
     * @param ByFixed|CartFixed|ToFixed $subject
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCalculate($subject, $rule, $item, $qty)
    {
        try {
            $websiteCurrency = $this->storeManager->getWebsite()->getBaseCurrency();
            $storeCurrency = $this->storeManager->getStore()->getBaseCurrency();
            // refresh before get
            $rule->load($rule->getId());

            $convertedDiscountAmount = $websiteCurrency->convert($rule->getDiscountAmount(), $storeCurrency);
            $rule->setDiscountAmount($convertedDiscountAmount);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return [$rule, $item, $qty];
    }
}
