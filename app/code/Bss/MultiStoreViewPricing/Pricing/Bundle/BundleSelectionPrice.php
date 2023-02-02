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
namespace Bss\MultiStoreViewPricing\Pricing\Bundle;

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Pricing\Price as CatalogPrice;

class BundleSelectionPrice extends \Magento\Bundle\Pricing\Price\BundleSelectionPrice
{
    /**
     * Get the price value for one of selection product.
     *
     * @return bool|float
     */
    public function getValue()
    {
        if (null !== $this->value) {
            return $this->value;
        }
        $product = $this->selection;
        $bundleSelectionKey = 'bundle-selection-'
            . ($this->useRegularPrice ? 'regular-' : '')
            . 'value-'
            . $product->getSelectionId();
        if ($product->hasData($bundleSelectionKey)) {
            return $product->getData($bundleSelectionKey);
        }

        $priceCode = $this->useRegularPrice ?
            \Magento\Bundle\Pricing\Price\BundleRegularPrice::PRICE_CODE :
            \Magento\Bundle\Pricing\Price\FinalPrice::PRICE_CODE;
        if ($this->bundleProduct->getPriceType() == Price::PRICE_TYPE_DYNAMIC) {
            // just return whatever the product's value is
            $value = $this->priceInfo
                ->getPrice($priceCode)
                ->getValue();

            if ($priceCode == \Magento\Bundle\Pricing\Price\FinalPrice::PRICE_CODE) {
                $value = $product->getFinalPrice();
            }
        } else {
            // don't multiply by quantity.  Instead just keep as quantity = 1
            $selectionPriceValue = $this->selection->getSelectionPriceValue();
            if ($this->product->getSelectionPriceType()) {
                // calculate price for selection type percent
                $price = $this->bundleProduct->getPriceInfo()
                    ->getPrice(CatalogPrice\RegularPrice::PRICE_CODE)
                    ->getValue();
                $product = clone $this->bundleProduct;
                $product->setFinalPrice($price);
                $this->eventManager->dispatch(
                    'catalog_product_get_final_price',
                    ['product' => $product, 'qty' => $this->bundleProduct->getQty()]
                );
                $price = $this->useRegularPrice ? $product->getData('price') : $product->getData('final_price');
                $value = $price * ($selectionPriceValue / 100);
            } else {
                // calculate price for selection type fixed
                $value = $this->priceCurrency->convert($selectionPriceValue);
            }
        }
        if (!$this->useRegularPrice) {
            $value = $this->discountCalculator->calculateDiscount($this->bundleProduct, $value);
        }
        $this->value = $this->priceCurrency->round($value);
        $product->setData($bundleSelectionKey, $this->value);

        return $this->value;
    }
}
