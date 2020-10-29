<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\ViewModel;

/**
 * Retrieves last order data for conversion
 */
class LastOrder implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Get last order from session
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * Gets sku list from order
     *
     * @return string
     */
    public function getOrderItemSkus() : string
    {
        $order = $this->getOrder();
        $skus = [];
        foreach ($order->getAllItems() as $item) {
            $skus[] = $item->getSku();
        }
        $skus = array_values(array_unique($skus));
        return json_encode($skus);
    }
}
