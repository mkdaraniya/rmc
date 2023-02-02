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
namespace Bss\MultiStoreViewPricing\Ui\Component\Sales\Listing\Column;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Store\Model\StoreManagerInterface;

class Price extends \Magento\Sales\Ui\Component\Listing\Column\Price
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceCurrencyInterface $priceFormatter
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceFormatter,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $uiComponentFactory, $priceFormatter, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $currencyCode = isset($item['base_currency_code']) ? $item['base_currency_code'] : null;

                try {
                    if (is_null($currencyCode)) {
                        $currencyCode = $this->storeManager->getStore((int) $item['store_id'])
                            ->getCurrentCurrency()
                            ->getCode();
                        $item['base_currency_code'] = $currencyCode;
                    }
                } catch (\Exception $e) {
                    $currencyCode = null;
                }

                $item[$this->getData('name')] = $this->priceFormatter->format(
                    $item[$this->getData('name')],
                    false,
                    null,
                    null,
                    $currencyCode
                );
            }
        }

        return $dataSource;
    }
}
