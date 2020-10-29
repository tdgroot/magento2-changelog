<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShoppingAds\Model\Indexer;

/**
 * Indexer for products that are needed to be removed from merchant center
 */
class RemoveIndexer implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var \Magento\GoogleShoppingAds\Model\ServiceDataRetriever
     */
    private $serviceDataRetriever;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\GoogleShoppingAds\Model\ServiceClient
     */
    private $serviceClient;
    /**
     * @var \Magento\GoogleShoppingAds\Model\Indexer\RemoveIndexer\RemoveRetriever
     */
    private $removeRetriever;
    /**
     * @var ProductCacheRemover
     */
    private $productCacheRemover;

    /**
     * @param \Magento\GoogleShoppingAds\Model\ServiceDataRetriever $serviceDataRetriever
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\GoogleShoppingAds\Model\ServiceClient $serviceClient
     * @param RemoveIndexer\RemoveRetriever $removeRetriever
     * @param ProductCacheRemover $productCacheRemover
     */
    public function __construct(
        \Magento\GoogleShoppingAds\Model\ServiceDataRetriever $serviceDataRetriever,
        \Psr\Log\LoggerInterface $logger,
        \Magento\GoogleShoppingAds\Model\ServiceClient $serviceClient,
        \Magento\GoogleShoppingAds\Model\Indexer\RemoveIndexer\RemoveRetriever $removeRetriever,
        ProductCacheRemover $productCacheRemover
    ) {
        $this->serviceDataRetriever = $serviceDataRetriever;
        $this->logger = $logger;
        $this->serviceClient = $serviceClient;
        $this->removeRetriever = $removeRetriever;
        $this->productCacheRemover = $productCacheRemover;
    }

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
        $this->process([]);
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $ids)
    {
        $this->process($ids);
    }

    /**
     * @inheritdoc
     */
    public function executeRow($id)
    {
        $this->process([$id]);
    }

    /**
     * @inheritdoc
     */
    public function execute($ids)
    {
        $this->process($ids);
    }

    /**
     * Main index processing action
     *
     * @param array|null $ids
     * @throws \Exception
     */
    private function process(array $ids)
    {
        try {
            $websiteConfigs = json_decode(
                $this->serviceDataRetriever->getWebsiteConfigs(),
                true
            );
            foreach ($websiteConfigs as $websiteConfig) {
                $removedIds = $this->removeRetriever
                    ->getRemovedIds($websiteConfig['channelAttributes']['webSiteId'], $ids);
                $this->productCacheRemover->execute($removedIds);
                $payload = json_encode([
                    'products' => array_values($removedIds)
                ]);
                $this->serviceClient->removeProducts($payload, $websiteConfig['channelId']);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            if (!$ids) {
                throw $e;
            }
        }
    }
}
