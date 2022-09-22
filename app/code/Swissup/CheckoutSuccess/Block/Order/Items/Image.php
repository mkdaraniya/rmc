<?php

namespace Swissup\CheckoutSuccess\Block\Order\Items;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link as ProductLink;
use Magento\Framework\View\Element\Context;
use Magento\Framework\Exception\NoSuchEntityException;

class Image extends AbstractBlock
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ImageBuilder
     */
    protected $imageBuilder;

    /**
     * @var ProductLink
     */
    protected $productLinks;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ImageBuilder               $imageBuilder
     * @param ProductLink                $productLink
     * @param Context                    $context
     * @param array                      $data
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ImageBuilder $imageBuilder,
        ProductLink $productLink,
        Context $context,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->imageBuilder = $imageBuilder;
        $this->productLinks = $productLink;
        parent::__construct($context, $data);
    }

    /**
     * Get product image for ordered item
     *
     * @param  \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    public function getImageHtml(\Magento\Sales\Model\Order\Item $item)
    {
        $product = $item->getProduct();
        $imageId = $this->getProductImageId();

        /**
         * For configurable product find simple product and use its image.
         */
        if ($item->getProductType() === 'configurable') {
            try {
                $simpleProduct = $this->productRepository->get($item->getSku());
                if ($this->hasImage($simpleProduct)) {
                    $product = $simpleProduct;
                }
            } catch (NoSuchEntityException $noEntityException) {
                $product = $product;
            }
        }

        /**
         * Get parent grouped product image in case its child is empty.
         */
        if (!$this->hasImage($product)) {
            $groupedIds = $this->productLinks->getParentIdsByChild(
                $product->getId(),
                ProductLink::LINK_TYPE_GROUPED
            );
            try {
                $product = $this->productRepository->getById(reset($groupedIds));
            } catch (NoSuchEntityException $noEntityException) {
                $product = $product;
            }
        }

        return $this->imageBuilder
            ->setProduct($product) // compatibility with Magento 2.2.6
            ->setImageId($imageId) // compatibility with Magento 2.2.6
            ->create()             // compatibility with Magento 2.2.6
            ->toHtml();
    }

    /**
     * @param  ProductInterface $product
     * @return boolean
     */
    private function hasImage(ProductInterface $product) {
        return $product->getImage() && $product->getImage() != 'no_selection';
    }
}
