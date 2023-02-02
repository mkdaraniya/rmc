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
 * @package    Bss_MultiStoreViewPricingCatalogRule
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingCatalogRule\Plugin\CatalogRule\Indexer;

/**
 * Product price re-calculation fixed amount for specific store view.
 */
class ProductPriceCalculator
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
     * Convert fixed amount to store view value.
     *
     * @param \Magento\CatalogRule\Model\Indexer\ProductPriceCalculator $subject
     * @param array $ruleData
     * @param null|array $productData
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCalculate($subject, $ruleData, $productData = null)
    {
        try {
            if (isset($ruleData['store_id'])) {
                if ($ruleData['action_operator'] == 'to_fixed' || $ruleData['action_operator'] == 'by_fixed') {
                    $websiteCurrency = $this->storeManager->getWebsite($ruleData['website_id'])->getBaseCurrency();
                    $storeCurrency = $this->storeManager->getStore($ruleData['store_id'])->getBaseCurrency();

                    $convertedDiscountAmount = $websiteCurrency->convert($ruleData['action_amount'], $storeCurrency);
                    $ruleData['action_amount'] = $convertedDiscountAmount;
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return [$ruleData, $productData];
    }
}
