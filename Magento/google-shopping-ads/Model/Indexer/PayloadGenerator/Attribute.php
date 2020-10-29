<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator;

use Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Attribute\AttributeRawValueRetriever;

/**
 * Attribute payload generator
 */
class Attribute implements \Magento\GoogleShoppingAds\Model\Indexer\PayloadGeneratorInterface
{
    const SKIPPED_ATTRIBUTES = [
        'category_ids',
        'media_gallery',
        'attribute_set_id',
        'old_id',
        'created_at',
        'updated_at',
        'links_purchased_separately',
        'price_view',
        'page_layout',
        'options_container',
    ];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Attribute\AdditionalAttributesInterface[]
     */
    private $additionalAttributes;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurableType;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Attribute\AttributeRawValueRetriever|null
     */
    private $attributeRawValueRetriever;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Attribute\AdditionalAttributesInterface[] $additionalAttributes
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param AttributeRawValueRetriever|null $attributeRawValueRetriever
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $additionalAttributes,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        AttributeRawValueRetriever $attributeRawValueRetriever = null
    ) {
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->additionalAttributes = $additionalAttributes;
        $this->configurableType = $configurableType;
        $this->productRepository = $productRepository;
        $this->attributeRawValueRetriever = $attributeRawValueRetriever
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(AttributeRawValueRetriever::class);
    }

    /**
     * @inheritdoc
     */
    public function generate(
        array $products,
        int $websiteId
    ) : array {
        $feeds = [];
        $stores = $this->storeManager->getStores(true);

        foreach ($products as $product) {
            $attributes = $this->extract($product, $stores);
            $sku = $attributes['sku']['admin']['value'];
            $feeds[$product->getSku()] = [
                'entityId' => $sku,
                'magentoId' => $product->getId(),
                'attributes' => $attributes
            ];
        }
        return $feeds;
    }

    /**
     * Returns array of product attributes.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $stores
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function extract(\Magento\Catalog\Model\Product $product, array $stores) : array
    {
        $attributes = $product->getAttributes();
        $parent = null;
        $parents = $this->configurableType->getParentIdsByChild($product->getId());
        if (isset($parents[0])) {
            $parent = $this->productRepository->getById($parents[0]);
        }
        $attributesArray = [];
        $attributeCodes = [];
        foreach ($attributes as $attribute) {
            if (in_array($attribute->getAttributeCode(), self::SKIPPED_ATTRIBUTES)) {
                continue;
            }
            $attributeCodes[] = $attribute->getAttributeCode();
        }
        /** @var \Magento\Store\Api\Data\StoreInterface $store */
        foreach ($stores as $store) {
            $rawValues = $this->getAttributeRawValues($product, $attributeCodes, (int)$store->getId());
            $parentRawValues = $parent ? $this->getAttributeRawValues($parent, $attributeCodes, (int)$store->getId())
                : [];

            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            foreach ($attributes as $attribute) {
                $attributeData = $this->getAttributeData($attribute, $product, $rawValues, $parent, $parentRawValues);

                if (count($attributeData)) {
                    $attributesArray[$attribute->getAttributeCode()][$store->getCode()] = $attributeData;
                }
            }
            foreach ($this->additionalAttributes as $additionalAttribute) {
                $attributesArray = array_merge_recursive(
                    $attributesArray,
                    $additionalAttribute->getAttributes($product, $store, $parent)
                );
            }
        }
        return $attributesArray;
    }

    /**
     * Get attribute raw values
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $attributeCodes
     * @param int $storeId
     * @return array
     */
    private function getAttributeRawValues(\Magento\Catalog\Model\Product $product, array $attributeCodes, int $storeId)
    {
        return $this->attributeRawValueRetriever->getAttributeRawValue(
            $product->getResource(),
            (int)$product->getId(),
            $attributeCodes,
            $storeId
        );
    }

    /**
     * Gets single attribute data for payload generation
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param \Magento\Catalog\Model\Product $product
     * @param array $rawValues
     * @param \Magento\Catalog\Model\Product|null $parent
     * @param array|null $parentRawValues
     * @return array
     */
    private function getAttributeData(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute,
        \Magento\Catalog\Model\Product $product,
        array $rawValues,
        \Magento\Catalog\Model\Product $parent = null,
        array $parentRawValues = null
    ) {
        if (in_array($attribute->getAttributeCode(), self::SKIPPED_ATTRIBUTES)) {
            return [];
        }
        $attributeData = [];
        $attributeCode = $attribute->getAttributeCode();
        try {
            if (!empty($rawValues[$attributeCode])) {
                $value = $this->getAttributeValue(
                    $rawValues[$attributeCode],
                    $attribute,
                    $product
                );
            } elseif ($parent && !empty($parentRawValues[$attributeCode])) {
                $value = $this->getAttributeValue(
                    $parentRawValues[$attributeCode],
                    $attribute,
                    $parent
                );
            } else {
                $value = '';
            }
            $attributeData = ['value' => strip_tags($value)];
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $attributeData;
    }

    /**
     * Gets attribute value for product based on a store id
     *
     * @param mixed $value
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    private function getAttributeValue(
        $value,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute,
        \Magento\Catalog\Model\Product $product
    ) : string {
        $attributeFrontend = $attribute->getFrontend();
        if (is_array($value)) {
            $value = $attributeFrontend->getValue($product);
        }
        if (in_array($attributeFrontend->getConfigField('input'), ['select', 'boolean'])) {
            $value = $attributeFrontend->getOption($value);
        } elseif ($attributeFrontend->getConfigField('input') === 'multiselect') {
            $value = $attributeFrontend->getOption($value);
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return html_entity_decode((string)$value, ENT_COMPAT | ENT_XHTML, 'UTF-8');
    }
}
