<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Indexer\RemoveIndexer;

/**
 * Processes deleted products
 */
class RemoveRetriever
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurableType;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->configurableType = $configurableType;
    }

    /**
     * Gets products collection and filters it by set of ids
     *
     * @param array $ids
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getCollection(array $ids) : \Magento\Catalog\Model\ResourceModel\Product\Collection
    {
        $collection = $this->collectionFactory->create();
        if (count($ids)) {
            $collection->addAttributeToFilter('entity_id', ['in' => $ids]);
        }
        return $collection;
    }

    /**
     * Returns ids that were deleted
     *
     * @param array $ids
     * @return array
     */
    private function getDeletedIds(array $ids) : array
    {
        $deletedIds = [];
        if (count($ids)) {
            $collection = $this->getCollection($ids);
            $loaded = $collection->getAllIds();
            $deletedIds = array_diff($ids, $loaded);
        }
        return $deletedIds;
    }

    /**
     * Gets products not in website
     *
     * @param int $websiteId
     * @param array $ids
     * @return array
     */
    private function getNotInWebsiteProducts(int $websiteId, array $ids) : array
    {
        $collection = $this->getCollection($ids);
        $collection->addWebsiteFilter($websiteId);
        $inWebsiteIds = $collection->getAllIds();
        $collection = $this->getCollection($ids);
        $notInWebsiteIds = $collection->getAllIds();

        return array_diff($notInWebsiteIds, $inWebsiteIds);
    }

    /**
     * Gets disabled products
     *
     * @param int $websiteId
     * @param array $ids
     * @return array
     */
    private function getDisabledProducts(int $websiteId, array $ids) : array
    {
        $collection = $this->getCollection($ids);
        $collection->addWebsiteFilter($websiteId);
        $collection->addAttributeToFilter('status', [
                'eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED
        ]);
        $disabledProducts = $collection->getAllIds();
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection->getItems() as $product) {
            if ($product->getTypeId() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                $disabledProducts = array_merge(
                    $disabledProducts,
                    $this->getInvisibleOrDisabledOfConfigurable($product)
                );
            }
        }
        return $disabledProducts;
    }

    /**
     * Gets invisible products
     *
     * @param int $websiteId
     * @param array $ids
     * @return array
     */
    private function getInvisibleProducts(int $websiteId, array $ids) : array
    {
        $collection = $this->getCollection($ids);
        $collection->addWebsiteFilter($websiteId);
        $collection->addAttributeToFilter('visibility', [
            'eq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE
        ]);
        $invisibleProducts = $collection->getAllIds();
        $excluded = [];
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection->getItems() as $product) {
            if ($product->getTypeId() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                $invisibleProducts = array_merge(
                    $invisibleProducts,
                    $this->getInvisibleOrDisabledOfConfigurable($product)
                );
            } else {
                $parents = $this->configurableType->getParentIdsByChild($product->getId());
                if (isset($parents[0])) {
                    /** @var \Magento\Catalog\Model\Product $parent */
                    $parent = $this->getCollection([$parents[0]])->getFirstItem();
                    if ($parent->getStatus() === \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
                    && $parent->getVisibility() !== \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE) {
                        $excluded[] = $product->getId();
                    }
                }
            }
        }
        return array_diff($invisibleProducts, $excluded);
    }

    /**
     * Gets invisible or disabled products of configurable
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    private function getInvisibleOrDisabledOfConfigurable(\Magento\Catalog\Model\Product $product) : array
    {
        $ids = [];
        $children = $this->configurableType->getUsedProducts($product);
        /** @var \Magento\Catalog\Model\Product $child */
        foreach ($children as $child) {
            if ($child->getStatus() === \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED
            || $child->getVisibility() === \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE
            || $child->getVisibility() == 0) {
                $ids[] = $child->getId();
            }
        }
        return $ids;
    }

    /**
     * Gets bundle products with dynamic price
     *
     * @param int $websiteId
     * @param array $ids
     * @return array
     */
    private function getBundlesWithDynamicPricing(int $websiteId, array $ids) : array
    {
        $collection = $this->getCollection($ids);
        $collection->addWebsiteFilter($websiteId);
        $collection->addAttributeToFilter('type_id', [
            'eq' => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ]);
        $collection->addAttributeToFilter('price_type', [
            'eq' => \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC
        ]);
        return $collection->getAllIds();
    }

    /**
     * Filters out ids of products that have been deleted
     *
     * @param int $websiteId
     * @param array $ids
     * @return array
     */
    public function getRemovedIds(int $websiteId, array $ids) : array
    {
        return array_unique(array_merge(
            $this->getDeletedIds($ids),
            $this->getNotInWebsiteProducts($websiteId, $ids),
            $this->getDisabledProducts($websiteId, $ids),
            $this->getInvisibleProducts($websiteId, $ids),
            $this->getBundlesWithDynamicPricing($websiteId, $ids)
        ));
    }
}
