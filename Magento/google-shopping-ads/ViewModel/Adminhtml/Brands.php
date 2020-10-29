<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\ViewModel\Adminhtml;

/**
 * ViewModel responsible for displaying all brands
 */
class Brands implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\GoogleShoppingAds\Model\AttributeValuesRetriever
     */
    private $attributeValuesRetriever;

    /**
     * @var \Magento\GoogleShoppingAds\Model\ServiceDataRetriever
     */
    private $serviceDataRetriever;

    /**
     * @param \Magento\GoogleShoppingAds\Model\ServiceDataRetriever $serviceDataRetriever
     * @param \Magento\GoogleShoppingAds\Model\AttributeValuesRetriever $attributeValuesRetriever
     */
    public function __construct(
        \Magento\GoogleShoppingAds\Model\ServiceDataRetriever $serviceDataRetriever,
        \Magento\GoogleShoppingAds\Model\AttributeValuesRetriever $attributeValuesRetriever
    ) {
        $this->attributeValuesRetriever = $attributeValuesRetriever;
        $this->serviceDataRetriever = $serviceDataRetriever;
    }

    /**
     * Gets model data
     *
     * @return string
     */
    public function getData() : string
    {
        $data = [];

        $mappings = $this->serviceDataRetriever->getMappings();
        if ($mappings) {
            $brandCodes = json_decode($mappings, true);
            $data = $this->attributeValuesRetriever->getValuesByCodes($brandCodes);
        }

        return json_encode($data);
    }
}
