<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Attribute;

/**
 * Gets raw attribute values from the database
 */
class AttributeRawValueRetriever
{
    /**
     * Collects static attributes
     *
     * @param \Magento\Catalog\Model\ResourceModel\AbstractResource $resource
     * @param array $staticAttributes
     * @param string $staticTable
     * @param int $entityId
     * @return array
     */
    private function getStaticAttributeValues(
        \Magento\Catalog\Model\ResourceModel\AbstractResource $resource,
        array $staticAttributes,
        string $staticTable,
        int $entityId
    ) : array {
        if (count($staticAttributes)) {
            $select = $resource->getConnection()->select()->from(
                $staticTable,
                $staticAttributes
            )->join(
                ['e' => $resource->getTable($resource->getEntityTable())],
                'e.' . $resource->getLinkField() . ' = ' . $staticTable . '.' . $resource->getLinkField()
            )->where(
                'e.entity_id = :entity_id'
            );
            return $resource->getConnection()->fetchRow($select, ['entity_id' => $entityId]);
        }
        return [];
    }

    /**
     * Collects typed attributes, performing separate SQL query for each attribute type table
     *
     * @param \Magento\Catalog\Model\ResourceModel\AbstractResource $resource
     * @param array $typedAttributes
     * @param int $entityId
     * @param int $storeId
     * @return array
     */
    private function getTypedAttributeValues(
        \Magento\Catalog\Model\ResourceModel\AbstractResource $resource,
        array $typedAttributes,
        int $entityId,
        int $storeId
    ) : array {
        $attributesData = [];

        foreach ($typedAttributes as $table => $attributes) {
            $select = $resource->getConnection()->select()
                ->from(['default_value' => $table], ['attribute_id'])
                ->join(
                    ['e' => $resource->getTable($resource->getEntityTable())],
                    'e.' . $resource->getLinkField() . ' = ' . 'default_value.' . $resource->getLinkField(),
                    ''
                )->where('default_value.attribute_id IN (?)', array_keys($attributes))
                ->where('e.entity_id = :entity_id')
                ->where('default_value.store_id = ?', 0);

            $valueExpr = $resource->getConnection()->getCheckSql(
                'store_value.value IS NULL',
                'default_value.value',
                'store_value.value'
            );
            $joinCondition = [
                $resource->getConnection()
                    ->quoteInto('store_value.attribute_id IN (?)', array_keys($attributes)),
                "store_value.{$resource->getLinkField()} = e.{$resource->getLinkField()}",
                'store_value.store_id = :store_id',
                'store_value.attribute_id = default_value.attribute_id'
            ];

            $select->joinLeft(
                ['store_value' => $table],
                implode(' AND ', $joinCondition),
                ['attr_value' => $valueExpr]
            );

            $result = $resource->getConnection()->fetchPairs($select, [
                'entity_id' => $entityId,
                'store_id' => $storeId
            ]);
            foreach ($result as $attrId => $value) {
                $attrCode = $typedAttributes[$table][$attrId];
                $attributesData[$attrCode] = $value;
            }
        }

        return $attributesData;
    }

    /**
     * Collects attribute raw values
     *
     * @param \Magento\Catalog\Model\ResourceModel\AbstractResource $resource
     * @param int $entityId
     * @param array $attributeCodes
     * @param int $storeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeRawValue(
        \Magento\Catalog\Model\ResourceModel\AbstractResource $resource,
        int $entityId,
        array $attributeCodes,
        int $storeId
    ) {
        $staticAttributes = [];
        $typedAttributes = [];
        $staticTable = '';

        foreach ($attributeCodes as $attributeCode) {
            /* @var $attribute \Magento\Catalog\Model\Entity\Attribute */
            $attribute = $resource->getAttribute($attributeCode);
            if (!$attribute) {
                continue;
            }
            $attrTable = $attribute->getBackend()->getTable();
            $isStatic = $attribute->getBackend()->isStatic();

            if ($isStatic) {
                $staticAttributes[] = $attributeCode;
                $staticTable = $attrTable;
            } else {
                $typedAttributes[$attrTable][$attribute->getId()] = $attributeCode;
            }
        }

        $staticAttributeValues = $this->getStaticAttributeValues($resource, $staticAttributes, $staticTable, $entityId);
        $typedAttributeValues = $this->getTypedAttributeValues($resource, $typedAttributes, $entityId, $storeId);

        return array_merge($staticAttributeValues, $typedAttributeValues);
    }
}
