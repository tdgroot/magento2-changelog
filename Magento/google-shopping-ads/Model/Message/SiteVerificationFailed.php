<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Message;

/**
 * Message that states failed site verification
 */
class SiteVerificationFailed implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function isDisplayed() : bool
    {
        $verificationConfigs = $this->scopeConfig->getValue(
            \Magento\GoogleShoppingAds\Cron\SiteVerification::PATH_VERIFICATION_CONFIGS
        );

        if ($verificationConfigs) {
            $verificationConfigs = json_decode($verificationConfigs, true);
            foreach ($verificationConfigs as $verificationConfig) {
                if (!$verificationConfig['isVerified']
                    && ($verificationConfig['attempts']
                        >= \Magento\GoogleShoppingAds\Cron\SiteVerification::ATTEMPTS)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getIdentity() : string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return md5('SCC_VERIFICATION_FAILED');
    }

    /**
     * @inheritdoc
     */
    public function getText()
    {
        return __(
            'Magento is unable to automatically claim and verify your site. For Google to approve your products, '
            . 'you must claim your store\'s URL. <a href="%1">Learn more on Google.</a>',
            'https://support.google.com/merchants/answer/176793'
        );
    }

    /**
     * @inheritdoc
     */
    public function getSeverity() : int
    {
        return self::SEVERITY_MAJOR;
    }
}
