<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShoppingAds\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for the \GuzzleHttp\Client object creation
 */
class GuzzleClientFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Instance name to create
     *
     * @var string
     */
    private $instanceName;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = \GuzzleHttp\Client::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \GuzzleHttp\Client
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, ['config' => $data]);
    }
}
