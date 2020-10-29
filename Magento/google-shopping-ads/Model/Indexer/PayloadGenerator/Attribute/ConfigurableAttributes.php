<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Attribute;

use Magento\Store\Api\Data\StoreInterface;

/**
 * Bundle attribute payload generator
 */
class ConfigurableAttributes implements AdditionalAttributesInterface
{
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
        $attributes = [];

        if ($parent) {
            $attributes = [
                'item_group_id' => [$store->getCode() => [
                    'value' => $parent->getId()
                ]]
            ];
        }

        return $attributes;
    }
}
