<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Plugin;

/**
 * Triggers on scope of products quantity changes
 */
class UpdateScopeOfItemsQty
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\GoogleShoppingAds\Model\GtagQuoteItemsHandler
     */
    private $updateCartItemsQty;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\GoogleShoppingAds\Model\GtagQuoteItemsHandler $updateCartItemsQty
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\GoogleShoppingAds\Model\GtagQuoteItemsHandler $updateCartItemsQty
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->updateCartItemsQty = $updateCartItemsQty;
    }

    /**
     * Send GTag on qty update
     *
     * @param \Magento\Checkout\Controller\Cart\UpdatePost $subject
     * @param \Closure $proceed
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute(
        \Magento\Checkout\Controller\Cart\UpdatePost $subject,
        \Closure $proceed
    ) {
        if ($this->scopeConfig->getValue(\Magento\GoogleShoppingAds\Cron\GTagRetriever::PATH_GTAG_CONFIG)) {
            $itemIds = array_keys($subject->getRequest()->getParam('cart'));
            $originalItems = $this->updateCartItemsQty->getQuoteItems($itemIds);
            $result = $proceed();
            $this->updateCartItemsQty->updateQuoteItemsQty($originalItems);
        } else {
            $result = $proceed();
        }

        return $result;
    }
}
