<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model;

/**
 * A model to perform requests to our services
 */
class ServiceClient
{
    const SERVICE_PATH = 'sales_channels/sales_channel_integration/service_path';
    const GATEWAY_PATH = 'sales_channels/sales_channel_integration/gateway_path';
    const GATEWAY_URL = 'sales_channels/sales_channel_integration/gateway_url';
    const ADDITIONAL_HEADERS_PATH = 'sales_channels/sales_channel_integration/additional_headers';
    const PRODUCT_FEED_URI = 'channels/{channelId}/feed/products';
    const CHANNELS_GET_URI = 'channels';
    const VERIFICATION_CODE_URI = 'channels/{channelId}/getVerificationCode';
    const VERIFICATION_REQUEST_URI = 'channels/{channelId}/verifyAndClaim';
    const GET_ADWORDS_ACCOUNT_URI = 'adwords/account';
    const GET_MAPPING_URI = 'channels/{channelId}/feed/attributeMapping';
    const REMOVE_PRODUCTS_URI = 'channels/{channelId}/product/removeBatch';

    /**
     * API headers
     * @var array
     */
    private $headers = [
        'Content-Type' => 'application/json',
        'Expect' => ''
    ];

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var UniqueIdManager
     */
    private $uniqueIdManager;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\GoogleShoppingAds\Model\GuzzleClientFactory $guzzleClientFactory
     * @param UniqueIdManager $uniqueIdManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\GoogleShoppingAds\Model\GuzzleClientFactory $guzzleClientFactory,
        \Magento\GoogleShoppingAds\Model\UniqueIdManager $uniqueIdManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->client = $guzzleClientFactory->create(
            [
                'base_uri' => $this->getServiceUrl(),
                'http_errors' => false
            ]
        );
        $this->uniqueIdManager = $uniqueIdManager;
    }

    /**
     * Get client data with status and body
     *
     * @param \GuzzleHttp\Psr7\Response $result
     *
     * @return array
     */
    private function getClientData($result) : array
    {
        return [
            'code' => $result->getStatusCode(),
            'body' => $result->getBody()->getContents()
        ];
    }

    /**
     * A generic method for passing through calls to SaaS
     *
     * @param string $method
     * @param string $url
     * @param string $payload
     * @return array
     */
    public function request(string $method, string $url, string $payload = '') : array
    {
        try {
            $result = $this->client->request($method, $url, ['headers' => $this->getHeaders(), 'body' => $payload]);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            //All 4xx and 5xx http codes are handled properly. Transport exceptions are forwarded to 400
            return [
                'code' => '400',
                'body' => $e->getMessage()
            ];
        }
        return $this->getClientData($result);
    }

    /**
     * An async method for passing through calls to SaaS
     *
     * @param string $method
     * @param string $url
     * @param string $payload
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function asyncRequest(
        string $method,
        string $url,
        string $payload = ''
    ) : \GuzzleHttp\Promise\PromiseInterface {
        return $this->client->requestAsync($method, $url, ['headers' => $this->getHeaders(), 'body' => $payload]);
    }

    /**
     * Gets mapping info for a channel
     *
     * @param string $channelId
     * @return array
     */
    public function getMapping(string $channelId) : array
    {
        $url = str_replace('{channelId}', $channelId, self::GET_MAPPING_URI);

        return $this->request('GET', $url);
    }

    /**
     * Gets adwords account info from service
     *
     * @return array
     */
    public function getAdwordsAccount() : array
    {
        return $this->request('GET', self::GET_ADWORDS_ACCOUNT_URI);
    }

    /**
     * Requests channel verification
     *
     * @param string $channelId
     * @return array
     */
    public function requestVerification(string $channelId) : array
    {
        $url = str_replace('{channelId}', $channelId, self::VERIFICATION_REQUEST_URI);

        return $this->request('POST', $url, '{}');
    }

    /**
     * Gets channel verification code
     *
     * @param string $channelId
     * @return array
     */
    public function getVerificationCode(string $channelId) : array
    {
        $url = str_replace('{channelId}', $channelId, self::VERIFICATION_CODE_URI);

        return $this->request('GET', $url);
    }

    /**
     * Get channels data
     *
     * @return array
     */
    public function getChannels() : array
    {
        return $this->request('GET', self::CHANNELS_GET_URI);
    }

    /**
     * Sends product feeds.
     *
     * @param string $payload
     * @param string $channelId
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function sendFeed(string $payload, string $channelId) : \GuzzleHttp\Promise\PromiseInterface
    {
        $url = str_replace('{channelId}', $channelId, self::PRODUCT_FEED_URI);

        return $this->asyncRequest('POST', $url, $payload);
    }

    /**
     * Get service URL from config
     *
     * @return string
     */
    private function getServiceUrl() : string
    {
        $gatewayUrl = trim((string)$this->scopeConfig->getValue(self::GATEWAY_URL), '/');
        $servicePath = trim((string)$this->scopeConfig->getValue(self::SERVICE_PATH), '/');

        return $gatewayUrl . '/' . $servicePath . '/';
    }

    /**
     * Get gateway key check URL from config
     *
     * @return string
     */
    private function getApiCheckUrl() : string
    {
        $gatewayUrl = trim((string)$this->scopeConfig->getValue(self::GATEWAY_URL), '/');
        $gatewayPath = trim((string)$this->scopeConfig->getValue(self::GATEWAY_PATH), '/');

        return $gatewayUrl . '/' . $gatewayPath . '/apikeycheck';
    }

    /**
     * Checks whether provided api key is set and valid
     *
     * @return bool
     */
    public function isKeyValid() : bool
    {
        $result = $this->request('GET', $this->getApiCheckUrl());

        if ($result['code'] >= 400 && $result['code'] < 600) {
            return false;
        }

        return true;
    }

    /**
     * Get headers for request
     */
    private function getHeaders()
    {
        $magentoKey = $this->scopeConfig
            ->getValue(\Magento\GoogleShoppingAds\Controller\Adminhtml\Index\MagentoGatewayCallback::PATH_MAGENTO_KEY);

        $additional = (string)$this->scopeConfig->getValue(self::ADDITIONAL_HEADERS_PATH);
        $additional = json_decode($additional, true);
        $additional = is_array($additional) ? $additional : [];

        return array_merge($this->headers, [
            'magento-api-key' => $magentoKey,
            'x-magento-unique-id' => $this->uniqueIdManager->get()
        ], $additional);
    }

    /**
     * Remove products from SaaS
     *
     * @param string $payload
     * @param string $channelId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function removeProducts(string $payload, string $channelId) : array
    {
        $url = str_replace('{channelId}', $channelId, self::REMOVE_PRODUCTS_URI);
        return $this->request('POST', $url, $payload);
    }
}
