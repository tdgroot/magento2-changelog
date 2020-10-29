<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Plugin;

use Magento\GoogleShoppingAds\Model\GtagQuoteItemsHandler;

/**
 * Triggers on product adding to cart
 */
class AddProductToCart
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\GoogleShoppingAds\Model\CookieSender
     */
    private $cookieSender;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurableType;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\GoogleShoppingAds\Model\CookieSender $cookieSender
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\GoogleShoppingAds\Model\CookieSender $cookieSender,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->cookieSender = $cookieSender;
        $this->productRepository = $productRepository;
        $this->configurableType = $configurableType;
    }

    /**
     * Send GTag on add product to cart event
     *
     * @param \Magento\Checkout\Controller\Cart\Add $subject
     * @param \Magento\Framework\Controller\Result\Redirect|void $result
     * @return \Magento\Framework\Controller\Result\Redirect|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterExecute(\Magento\Checkout\Controller\Cart\Add $subject, $result)
    {
        if ($this->scopeConfig->getValue(\Magento\GoogleShoppingAds\Cron\GTagRetriever::PATH_GTAG_CONFIG)) {
            $superAttributes = $subject->getRequest()->getParam('super_attribute');
            if (!$this->isSwatchOptionsSelected($superAttributes)) {
                return $result;
            }

            $productId = (int)$subject->getRequest()->getParam('product');
            $product = $this->configurableType->getProductByAttributes($superAttributes, $this->productRepository->getById($productId));
            if ($product && $product->getId()) {
                $itemId = $product->getId();
            } elseif ((int)$subject->getRequest()->getParam('selected_configurable_option')) {
                $itemId = (int)$subject->getRequest()->getParam('selected_configurable_option');
            } else {
                $itemId = $productId;
            }

            $addedQuantity = (int)$subject->getRequest()->getParam('qty');
            $item = $this->productRepository->getById($itemId);
            $this->cookieSender->sendCookie(
                GtagQuoteItemsHandler::REGISTRY_NAMESPACE_ADD_TO_CART,
                [[
                    'sku'   => $item->getSku(),
                    'name'  => $item->getName(),
                    'price' => $item->getPrice(),
                    'qty'   => $addedQuantity,
                ]]
            );
        }
        return $result;
    }

    private function isSwatchOptionsSelected($attribute): bool
    {
        $isOptionsSelected = true;
        if (is_array($attribute) && !empty($attribute)) {
            $optionsSelected = array_filter($attribute, function($value) {
                return $value !== null & $value !== '';
            });

            if (count($optionsSelected) !== count($attribute)) {
                $isOptionsSelected = false;
            }
        }
        return $isOptionsSelected;
    }
}
