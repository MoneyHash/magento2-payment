<?php
/*
 *
  * Copyright © 2025 MoneyHash. All rights reserved.
  *
  * Developed by: Ahmed Allam

  *
  * Project: MoneyHash Payment Integration Extension for Magento 2
  *
  * NOTICE OF LICENSE
  * This source file is subject to the proprietary license that is bundled
  * with this package in the file LICENSE.txt. It is also available through
  * the world-wide-web at:
  * https://moneyhash.io/


 */

namespace MoneyHash\Payment\Gateway\Request\Builder;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Payment\Model\MethodInterface;

class WebhookData implements BuilderInterface
{

    public function __construct(
        private readonly UrlInterface $url
    )
    {
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws NoSuchEntityException
     */
    public function build(array $buildSubject)
    {

        return [
            'webhook_url' => $this->url->getUrl('moneyhash/payment/webhook', ['_secure' => true]),
            'successful_redirect_url' => $this->url->getUrl('moneyhash/payment/success', ['_secure' => true]),
            'failed_redirect_url' => $this->url->getUrl('moneyhash/payment/fail', ['_secure' => true]),
            'pending_external_action_redirect_url' => $this->url->getUrl('moneyhash/payment/success', ['_secure' => true])
        ];
    }
}
