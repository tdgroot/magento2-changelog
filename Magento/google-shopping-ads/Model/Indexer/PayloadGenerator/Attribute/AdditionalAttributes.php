<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Attribute;

use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Additional attribute payload generator
 */
class AdditionalAttributes implements AdditionalAttributesInterface
{
    /**
     * Array for caching store url models
     *
     * @var array
     */
    private $storeUrlCache = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\UrlFactory
     */
    private $urlFactory;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->urlFactory = $urlFactory;
        $this->urlFinder = $urlFinder;
    }

    /**
     * Get url model for specific store
     *
     * @param StoreInterface $store
     * @return UrlInterface
     */
    private function getStoreUrl(StoreInterface $store) : UrlInterface
    {
        if (!isset($this->storeUrlCache[$store->getCode()])) {
            $this->storeUrlCache[$store->getCode()] = $this->urlFactory->create()->setScope($store);
        }
        return $this->storeUrlCache[$store->getCode()];
    }

    /**
     * Get url for a product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param StoreInterface $store
     * @return string
     */
    private function getProductUrl(
        \Magento\Catalog\Model\Product $product,
        StoreInterface $store
    ) : string {
        if (!$store->getId()) {
            return '';
        }
        $routePath = '';
        $routeParams['_secure'] = true;
        $routeParams['_nosid'] = true;
        $url = $this->getStoreUrl($store);

        $requestPath = $product->getRequestPath();
        if (empty($requestPath) && $requestPath !== false) {
            $filterData = [
                UrlRewrite::ENTITY_ID => $product->getId(),
                UrlRewrite::ENTITY_TYPE => \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE,
                UrlRewrite::STORE_ID => $store->getId(),
            ];
            $rewrite = $this->urlFinder->findOneByData($filterData);
            if ($rewrite) {
                $requestPath = $rewrite->getRequestPath();
                $product->setRequestPath($requestPath);
            }
        }

        if (!empty($requestPath)) {
            $routeParams['_direct'] = $requestPath;
        } else {
            $routePath = 'catalog/product/view';
            $routeParams['id'] = $product->getId();
            $routeParams['s'] = $product->getUrlKey();
        }

        return $url->getUrl($routePath, $routeParams);
    }

    /**
     * Gets additional attributes for google
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param StoreInterface $store
     * @param \Magento\Catalog\Model\Product|null $parent
     * @return array
     */
    public function getAttributes(
        \Magento\Catalog\Model\Product $product,
        StoreInterface $store,
        \Magento\Catalog\Model\Product $parent = null
    ) : array {
        if ($parent) {
            $product = $parent;
        }

        return [
            'product_url' => [$store->getCode() => [
                'value' => $this->getProductUrl($product, $store)
            ]],
            'image_url' => [$store->getCode() => [
                'value' => $product->getImage()
                    ? $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA, true) . 'catalog/product'
                    . $product->getImage()
                    : ''
            ]],
            'category_name' => [$store->getCode() => [
                'value' => $this->getCategoryName($product)
            ]]
        ];
    }

    /**
     * Gets product closest category name
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    private function getCategoryName(\Magento\Catalog\Model\Product $product) : string
    {
        try {
            $categoryCollection = $this->collectionFactory->create();
            $categoryCollection->addAttributeToSelect('*');
            $categoryCollection->addIsActiveFilter();
            $categoryCollection->setPageSize(1);
            $categoryCollection->addOrder('level', \Magento\Framework\Data\Collection::SORT_ORDER_DESC);
            $categoryCollection->addAttributeToFilter('entity_id', $product->getCategoryIds());
            return $categoryCollection->getFirstItem()->getId() ?? '';
        } catch (\Exception $e) {
            return '';
        }
    }
}
