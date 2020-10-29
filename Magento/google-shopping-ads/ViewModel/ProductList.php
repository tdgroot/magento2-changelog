<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\ViewModel;

use Magento\Catalog\Model\Product;

/**
 * Retrieves skus of product list block
 */
class ProductList implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * Get serialized string of loaded products SKUs
     *
     * @param \Magento\Catalog\Block\Product\ListProduct $block
     * @return string
     */
    public function getSkuList(\Magento\Catalog\Block\Product\ListProduct $block) : string
    {
        $collection = $block->getLoadedProductCollection();
        $skus = [];
        /** @var Product $product */
        foreach ($collection->getItems() as $product) {
            $skus[] = $this->getProductSku($product);
        }
        return json_encode($skus);
    }

    /**
     * Get correct product sku for GTag
     *
     * @param Product $product
     * @return string
     */
    public function getProductSku(Product $product) : string
    {
        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return (string)$product->getId();
        }
        return $product->getSku();
    }
}
