<?php declare(strict_types=1);
/*
 *
  * Copyright © 2025 MoneyHash. All rights reserved.
  *
  * Developed by: Ahmed Allam
  * Contact: mageserv.ltd@gmail.com (+20 102 0763062)
  *
  * Project: MoneyHash Payment Integration Extension for Magento 2
  *
  * NOTICE OF LICENSE
  * This source file is subject to the proprietary license that is bundled
  * with this package in the file LICENSE.txt. It is also available through
  * the world-wide-web at:
  * https://moneyhash.io/


 */

namespace MoneyHash\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Gateway\Config\Config;

class ConfigProvider implements ConfigProviderInterface
{
    public const CODE_ALL = "moneyhash_all";
    public const INTENT_ID = "intent_id";
    public const ORDER_INSTRUCTIONS = "order_instructions";
    public const PROVIDER_TRANSACTION_FIELDS = "provider_transaction_fields";
    public const STAGING_EMBED_URL = "https://stg-embed.moneyhash.io/embed/payment/";
    public const EMBED_URL = "https://embed.moneyhash.io/embed/payment/";

    public function __construct(
        private readonly Config $config
    ) {
    }
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE_ALL => [
                    'public_key' => $this->config->getValue('public_key'),
                    'is_test_mode' => (int)$this->config->getValue('test_mode'),
                    'language' => $this->config->getValue('language'),
                    'integration_type' => $this->config->getValue('integration_type'),
                    'embed_url' => $this->config->getValue('test_mode') ? self::STAGING_EMBED_URL : self::EMBED_URL
                ]
            ]
        ];
    }
}
