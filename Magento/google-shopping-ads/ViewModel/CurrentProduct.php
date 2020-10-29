<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\ViewModel;

/**
 * Retrieves current product from registry
 */
class CurrentProduct implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {

        $this->registry = $registry;
    }

    /**
     * Get current product from registry
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('product');
    }

    /**
     * Get current category from registry
     *
     * @return string
     */
    public function getCurrentCategoryName() : string
    {
        $category = $this->registry->registry('current_category');

        if (!$category) {
            return '';
        }

        return $category->getName();
    }

    /**
     * Get child products sku mapped to options
     *
     * @return string
     */
    public function getConfigurableProductOptions() : string
    {
        $options = [];
        $product = $this->getCurrentProduct();
        $configurableType = $product->getTypeInstance();

        $attributes = $configurableType->getConfigurableAttributes($product);
        $childProducts = $configurableType->getUsedProducts($product);

        foreach ($childProducts as $childProduct) {
            foreach ($attributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $childProduct->getData($productAttribute->getAttributeCode());
                $options[$childProduct->getSku()][$productAttributeId] = $attributeValue;
            }
        }

        return json_encode($options);
    }
}
