<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaypalReCaptcha\Plugin;

use Magento\PaypalReCaptcha\Model\Config;
use MSP\ReCaptcha\Model\LayoutSettings as ReCaptchaLayoutSettings;

/**
 * Provides PayPal reCaptcha configuration.
 */
class LayoutSettings
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Adds PayPal reCaptcha configuration parameter.
     *
     * @param ReCaptchaLayoutSettings $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCaptchaSettings(
        ReCaptchaLayoutSettings $subject,
        array $result
    ) {
        $result['enabled']['paypal'] = $this->config->isEnabledFrontendPaypal();

        return $result;
    }
}
