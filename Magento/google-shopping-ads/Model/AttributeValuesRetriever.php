<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model;

/**
 * Retrieves attribute values
 */
class AttributeValuesRetriever
{
    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Get attributes value by attribute codes
     *
     * @param array $codes
     * @return array
     */
    public function getValuesByCodes(array $codes) : array
    {
        $values = [];

        foreach ($codes as $code) {
            try {
                $attribute = $this->attributeRepository->get(
                    \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
                    $code
                );
                $values += $this->getValues($attribute);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                //If the attribute is not found (i.e. was deleted) just ignore it and go to the next one
                continue;
            }
        }
        return $values;
    }

    /**
     * Get attributes values by attribute
     *
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @return array
     */
    private function getValues(\Magento\Eav\Api\Data\AttributeInterface $attribute) : array
    {
        $return = [];

        $options = $attribute->getOptions();
        if (count($options)) {
            foreach ($options as $option) {
                if ($option->getValue()) {
                    $return[$attribute->getAttributeCode()][] = [
                        'value' => $option->getValue(),
                        'label' => $option->getLabel()
                    ];
                }
            }
        } else {
            $return[$attribute->getAttributeCode()] = $this->getDistinctValuesDb($attribute);
        }

        return $return;
    }

    /**
     * Get attributes value for all products by attribute
     *
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @return array
     */
    private function getDistinctValuesDb(\Magento\Eav\Api\Data\AttributeInterface $attribute) : array
    {
        $attributeValues = [];
        $attributeCode = $attribute->getAttributeCode();
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect($attributeCode)
            ->addAttributeToFilter($attributeCode, ['notnull' => true])
            ->getSelect()->group($attributeCode);
        foreach ($collection->getColumnValues($attributeCode) as $value) {
            $attributeValues[] = [
                'value' => $value,
                'label' => $value
            ];
        }
        return $attributeValues;
    }
}
