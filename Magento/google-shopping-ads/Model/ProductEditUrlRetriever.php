<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model;

/**
 * Gets product edit urls by sku
 */
class ProductEditUrlRetriever
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->url = $url;
    }

    /**
     * Get edit product URLs by the SKUs array
     *
     * @param array $skus
     * @return array
     */
    public function getProductEditUrls(array $skus) : array
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToFilter('sku', ['in' => $skus]);
        $data = [];
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection->getItems() as $product) {
            $data[$product->getSku()] = $this->url
                ->getUrl('catalog/product/edit', ['id' => $product->getId()]);
        }
        return $data;
    }
}
