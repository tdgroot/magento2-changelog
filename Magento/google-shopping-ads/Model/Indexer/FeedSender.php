<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Indexer;

/**
 * Product feed sender
 */
class FeedSender
{
    /**
     * Path for logs settings
     */
    const PATH_LOGS_CONFIG = 'sales_channels/sales_channel_integration/enable_service_logs';

    /**
     * @var PayloadGeneratorInterface[]
     */
    private $payloadGenerators;

    /**
     * @var \Magento\GoogleShoppingAds\Model\ServiceClient
     */
    private $serviceClient;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\GoogleShoppingAds\Model\Indexer\ChangeDetector
     */
    private $changeDetector;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param array $payloadGenerators
     * @param \Magento\GoogleShoppingAds\Model\ServiceClient $serviceClient
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\GoogleShoppingAds\Model\Indexer\ChangeDetector $changeDetector
     */
    public function __construct(
        array $payloadGenerators,
        \Magento\GoogleShoppingAds\Model\ServiceClient $serviceClient,
        \Psr\Log\LoggerInterface $logger,
        \Magento\GoogleShoppingAds\Model\Indexer\ChangeDetector $changeDetector,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->payloadGenerators = $payloadGenerators;
        $this->serviceClient = $serviceClient;
        $this->logger = $logger;
        $this->changeDetector = $changeDetector;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Gets and encodes payload from generators
     *
     * @param array $products
     * @param int $websiteId
     * @return string
     * @throws \Exception
     */
    private function getPayload(array $products, int $websiteId) : string
    {
        $payload = [];
        foreach ($this->payloadGenerators as $payloadGenerator) {
            $payload = array_replace_recursive($payload, $payloadGenerator->generate(
                $products,
                $websiteId
            ));
        }
        $this->changeDetector->filterUnchanged($payload);
        if (!count($payload)) {
            return '';
        }
        return json_encode(['products' => array_values($payload)]);
    }

    /**
     * Sends product feed to SaaS
     *
     * @param array $products
     * @param string $channelId
     * @param int $websiteId
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function sendFeed(array $products, string $channelId, int $websiteId) : \GuzzleHttp\Promise\PromiseInterface
    {
        $logger = $this->logger;
        $logsEnabled = $this->scopeConfig->getValue(self::PATH_LOGS_CONFIG);
        $uniqId = uniqid('', true);
        $payload = $this->getPayload($products, $websiteId);
        if (!$payload) {
            return new \GuzzleHttp\Promise\FulfilledPromise(null);
        }
        if ($logsEnabled) {
            $logger->info('Request ID: ' . $uniqId . ' Payload: ' . $payload);
        }
        return $this->serviceClient->sendFeed($payload, $channelId)
            ->then(function (\GuzzleHttp\Psr7\Response $response) use ($uniqId, $logger, $logsEnabled) {
                if ($logsEnabled) {
                    $logger->info('Response ID: ' . $uniqId . ' Response Code: ' . $response->getStatusCode()
                        . ' Response Body: ' . $response->getBody()->getContents());
                }
                if ($response->getStatusCode() !== 200) {
                    throw new \Magento\Framework\Exception\RemoteServiceUnavailableException(
                        __('Something went wrong')
                    );
                }
            }, function () {
                throw new \Magento\Framework\Exception\RemoteServiceUnavailableException(
                    __('Google Service is unavailable')
                );
            });
    }
}
