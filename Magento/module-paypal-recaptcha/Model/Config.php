<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaypalReCaptcha\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use MSP\ReCaptcha\Model\Config as ReCaptchaConfig;

/**
 * Configuration
 */
class Config
{
    /**
     * Enables reCaptcha on PayPal PayflowPro payment form.
     */
    const XML_PATH_ENABLED_FRONTEND_PAYPAL = 'msp_securitysuite_recaptcha/frontend/enabled_paypal';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ReCaptchaConfig
     */
    private $reCaptchaConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ReCaptchaConfig $reCaptchaConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ReCaptchaConfig $reCaptchaConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->reCaptchaConfig = $reCaptchaConfig;
    }

    /**
     * Return true if enabled on frontend PayPal PayflowPro payment form.
     *
     * @return bool
     */
    public function isEnabledFrontendPaypal()
    {
        if (!$this->reCaptchaConfig->isEnabledFrontend()) {
            return false;
        }

        return (bool) $this->scopeConfig->getValue(static::XML_PATH_ENABLED_FRONTEND_PAYPAL);
    }
}
