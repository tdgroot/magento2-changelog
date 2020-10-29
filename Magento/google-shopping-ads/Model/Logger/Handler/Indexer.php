<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Logger\Handler;

/**
 * Indexer log handler
 */
class Indexer extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Indexer log file
     */
    const FILENAME = '/var/log/google_indexer.log';

    /**
     * @var string
     */
    protected $fileName = self::FILENAME;

    /**
     * @var int
     */
    protected $loggerType = \Monolog\Logger::DEBUG;
}
