<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator;

/**
 * Price payload generator
 */
class Price implements \Magento\GoogleShoppingAds\Model\Indexer\PayloadGeneratorInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function generate(
        array $products,
        int $websiteId
    ) : array {
        $feeds = [];
        $currentStore = $this->storeManager->getStore();
        $store = $this->getDefaultStoreByWebsiteId($websiteId);
        $this->storeManager->setCurrentStore($store);
        $currencyCode = $store->getBaseCurrencyCode();
        foreach ($products as $product) {
            $feeds[$product->getSku()] = $this->map($product, $currencyCode);
        }
        $this->storeManager->setCurrentStore($currentStore);
        return $feeds;
    }

    /**
     * Get product price payload
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $currencyCode
     * @return array
     */
    private function map(\Magento\Catalog\Model\Product $product, string $currencyCode) : array
    {
        $regular = $this->getPriceAndAdjustments($product, 'regular_price');
        $special = $this->getPriceAndAdjustments($product, 'final_price');
        $map = [
            'entityId' => $product->getSku(),
            'magentoId' => $product->getId(),
            'prices' => [
                'regularPrice' => [
                    'amount' => $regular['price'],
                    'currency' => $currencyCode,
                    'adjustment' => [
                        'amount' => $regular['adjustments'],
                        'currency' => $currencyCode,
                    ]
                ],
                'specialPrice' => [
                    'amount' => $special['price'],
                    'currency' => $currencyCode,
                    'adjustment' => [
                        'amount' => $special['adjustments'],
                        'currency' => $currencyCode,
                    ]
                ],
            ]
        ];
        if (in_array($product->getTypeId(), [
            \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
            \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
        ])) {
            $map['prices']['shippingPrice'] = ['amount' => 0];
        }
        return $map;
    }

    /**
     * Get default store object by website ID
     *
     * @param int $websiteId
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getDefaultStoreByWebsiteId(int $websiteId) : \Magento\Store\Api\Data\StoreInterface
    {
        $website = $this->storeManager->getWebsite($websiteId);
        $group = $this->storeManager->getGroup($website->getDefaultGroupId());
        return $this->storeManager->getStore($group->getDefaultStoreId());
    }

    /**
     * Get price and adjustments for a product based on price code
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceCode
     * @return array
     */
    private function getPriceAndAdjustments(
        \Magento\Catalog\Model\Product $product,
        string $priceCode
    ) : array {
        $price = $product->getPriceInfo()->getPrice($priceCode)->getValue();
        $tax = $product->getPriceInfo()->getAdjustment('tax')->extractAdjustment($price, $product);
        $weee = $product->getPriceInfo()->getAdjustment('weee')->applyAdjustment(0, $product);
        $weeeTax = $product->getPriceInfo()->getAdjustment('weee_tax')->applyAdjustment(0, $product);
        return [
            'price' => ($price + $weee + $weeeTax),
            'adjustments' => ($tax + $weeeTax)
        ];
    }
}
