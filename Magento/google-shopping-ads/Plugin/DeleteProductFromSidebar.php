<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Plugin;

use Magento\GoogleShoppingAds\Model\GtagQuoteItemsHandler;

/**
 * Triggers on product removing from minicart
 */
class DeleteProductFromSidebar
{
    /**
     * @var \Magento\GoogleShoppingAds\Model\CookieSender
     */
    private $cookieSender;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\GoogleShoppingAds\Model\CookieSender $cookieSender
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\GoogleShoppingAds\Model\CookieSender $cookieSender,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->cookieSender = $cookieSender;
        $this->scopeConfig = $scopeConfig;
        $this->cart = $cart;
    }

    /**
     * Send GTag on removing product from cart event
     *
     * @param \Magento\Checkout\Controller\Sidebar\RemoveItem $subject
     * @param \Magento\Framework\App\Response\Http $result
     * @return \Magento\Framework\App\Response\Http
     */
    public function afterExecute(
        \Magento\Checkout\Controller\Sidebar\RemoveItem $subject,
        \Magento\Framework\App\Response\Http $result
    ): \Magento\Framework\App\Response\Http {
        if ($this->scopeConfig->getValue(\Magento\GoogleShoppingAds\Cron\GTagRetriever::PATH_GTAG_CONFIG)) {
            $itemId = (int)$subject->getRequest()->getParam('item_id');
            $quoteItems = $this->cart->getQuote();
            $item = $quoteItems->getItemById($itemId);
            $this->cookieSender->sendCookie(
                GtagQuoteItemsHandler::REGISTRY_NAMESPACE_REMOVE_FROM_CART,
                [[
                    'sku'   => $item->getSku(),
                    'name'  => $item->getName(),
                    'price' => $item->getPrice(),
                    'qty'   => $item->getQty()
                ]]
            );
        }

        return $result;
    }
}
