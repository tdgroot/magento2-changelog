<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShoppingAds\Model\Indexer;

/**
 * Universal feed indexer
 */
class FeedIndexer implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    const INDEXER_ATTRIBUTE_ID = 'scconnector_google_attributes';
    const INDEXER_PRICE_ID = 'scconnector_google_prices';
    const INDEXER_INVENTORY_ID = 'scconnector_google_inventory';

    /**
     * @var \Magento\GoogleShoppingAds\Model\ProductRetrieverInterface[]
     */
    private $productRetrievers;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\GoogleShoppingAds\Model\ServiceDataRetriever
     */
    private $serviceDataRetriever;

    /**
     * @var \Magento\GoogleShoppingAds\Model\Indexer\RemoveIndexer\RemoveRetriever
     */
    private $removeRetriever;

    /**
     * @var FeedSender
     */
    private $feedSender;

    /**
     * @param \Magento\GoogleShoppingAds\Model\ProductRetrieverInterface[] $productRetrievers
     * @param RemoveIndexer\RemoveRetriever $removeRetriever
     * @param \Magento\GoogleShoppingAds\Model\ServiceDataRetriever $serviceDataRetriever
     * @param \Psr\Log\LoggerInterface $logger
     * @param FeedSender $feedSender
     */
    public function __construct(
        $productRetrievers,
        \Magento\GoogleShoppingAds\Model\Indexer\RemoveIndexer\RemoveRetriever $removeRetriever,
        \Magento\GoogleShoppingAds\Model\ServiceDataRetriever $serviceDataRetriever,
        \Psr\Log\LoggerInterface $logger,
        \Magento\GoogleShoppingAds\Model\Indexer\FeedSender $feedSender
    ) {
        $this->removeRetriever = $removeRetriever;
        $this->productRetrievers = $productRetrievers;
        $this->logger = $logger;
        $this->serviceDataRetriever = $serviceDataRetriever;
        $this->feedSender = $feedSender;
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
     * @param array $ids
     * @throws \Exception
     */
    private function process(array $ids)
    {
        try {
            $websiteConfigs = json_decode(
                $this->serviceDataRetriever->getWebsiteConfigs(),
                true
            );
            $this->processWebsites($websiteConfigs, $ids);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            if (!$ids) {
                throw $e;
            }
        }
    }

    /**
     * Here we process all the needed products website by website in a chunks of defined size.
     *
     * @param array $configs
     * @param array $ids
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    private function processWebsites(array $configs, array $ids)// phpcs:ignore Generic.Metrics.NestingLevel
    {
        /** @var \GuzzleHttp\Promise\PromiseInterface[] $promises */
        $promises = [];

        foreach ($configs as $websiteConfig) {
            $excludedIds = $this->removeRetriever
                ->getRemovedIds($websiteConfig['channelAttributes']['webSiteId'], $ids);
            foreach ($this->productRetrievers as $productRetriever) {
                $productPage = 1;
                $productLoopExit = false;
                $lastProductId = 0;
                while (!$productLoopExit) {
                    $products = $productRetriever->retrieve($excludedIds, $ids, $productPage);
                    $productPage++;
                    if (!count($products)
                        || (array_values(array_slice($products, -1))[0]->getId() == $lastProductId)
                    ) {
                        $productLoopExit = true;
                    } else {
                        $promises[] = $this->feedSender->sendFeed(
                            $products,
                            $websiteConfig['channelId'],
                            (int)$websiteConfig['channelAttributes']['webSiteId']
                        );
                        $lastProductId = array_values(array_slice($products, -1))[0]->getId();
                    }
                }
            }
        }

        $this->waitForComplete($promises);
    }

    /**
     * Wait for all requests to complete
     *
     * @param \GuzzleHttp\Promise\PromiseInterface[] $promises
     */
    private function waitForComplete(array $promises)
    {
        foreach ($promises as $promise) {
            $promise->wait(true);
        }
    }
}
