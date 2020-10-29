<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model;

/**
 * Handle Gtag cart items information
 */
class GtagQuoteItemsHandler
{
    const REGISTRY_NAMESPACE_ADD_TO_CART = 'scconnector_products_addtocart';
    const REGISTRY_NAMESPACE_REMOVE_FROM_CART = 'scconnector_products_removefromcart';

    /**
     * @var \Magento\GoogleShoppingAds\Model\CookieSender
     */
    private $cookieSender;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @param \Magento\GoogleShoppingAds\Model\CookieSender $cookieSender
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \Magento\GoogleShoppingAds\Model\CookieSender $cookieSender,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->cookieSender = $cookieSender;
        $this->cart = $cart;
    }

    /**
     * Get quote items by their ids
     *
     * @param array $originalIds
     * @return array
     */
    public function getQuoteItems(array $originalIds): array
    {
        $items = [];
        $quote = $this->cart->getQuote();
        foreach ($originalIds as $itemId) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            $item = $quote->getItemById($itemId);
            $items[$item->getId()] = [
                'sku'   => $item->getSku(),
                'name'  => $item->getName(),
                'price' => $item->getProduct()->getPrice(),
                'qty' => $item->getQty() ? (double)$item->getQty() : null
            ];
        }

        return $items;
    }

    /**
     * Send Gtag with updated items qty to cookies
     *
     * @param array $originalItems
     * @return void
     */
    public function updateQuoteItemsQty(array $originalItems)
    {
        $updatedItems = $this->getItemsToUpdate($originalItems);
        foreach ($updatedItems as $namespace => $gTagData) {
            $this->cookieSender->sendCookie(
                $namespace,
                $gTagData
            );
        }
    }

    /**
     * Get updated quote items and provide this information to cookies sender
     *
     * @param array $originalItems
     * @return array
     */
    private function getItemsToUpdate(array $originalItems): array
    {
        $updatedItems = [];
        $newItems = $this->getQuoteItems(array_keys($originalItems));
        foreach ($originalItems as $itemId => $item) {
            $newQty = isset($newItems[$itemId]) ? $newItems[$itemId]['qty'] : 0;
            $originalQty = $item['qty'];

            if ($newQty > 0 && ($originalQty - $newQty) != 0) {
                $action = $originalQty < $newQty
                    ? self::REGISTRY_NAMESPACE_ADD_TO_CART
                    : self::REGISTRY_NAMESPACE_REMOVE_FROM_CART;

                $updatedItems[$action][] = [
                    'sku'   => $item['sku'],
                    'name'  => $item['name'],
                    'price' => $item['price'],
                    'qty'   => abs($originalQty - $newQty),
                ];
            }
        }

        return $updatedItems;
    }
}
