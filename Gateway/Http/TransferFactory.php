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

namespace MoneyHash\Payment\Gateway\Http;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use MoneyHash\Payment\Model\Ui\ConfigProvider;

class TransferFactory implements TransferFactoryInterface
{
    public const BASE_API_URL = 'https://web.moneyhash.io/api/v.4/';
    public const STAGING_API_URL = 'https://staging-web.moneyhash.io/api/v1.4/';
    /**
     * @param TransferBuilder $transferBuilder
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        private readonly TransferBuilder $transferBuilder,
        private readonly PaymentHelper $paymentHelper,
        private readonly string $method = "POST"
    ) {
    }
    private function getApiUrl()
    {
        $isTestMode = (bool) $this->paymentHelper->getMethodInstance(ConfigProvider::CODE_ALL)->getConfigData("test_mode");
        return $isTestMode ? self::STAGING_API_URL : self::BASE_API_URL;
    }
    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     * @throws LocalizedException
     */
    public function create(array $request)
    {
        $builder =  $this->transferBuilder
            ->setBody($request)
            ->setMethod($this->method)
            ->setHeaders([
                'content-type' => 'application/json',
                'X-Api-Key' => $this->paymentHelper->getMethodInstance(ConfigProvider::CODE_ALL)->getConfigData("api_key")
            ])
            ->setUri($this->getApiUrl());
        if (!empty($request['headers'])) {
            $builder->setHeaders($request['headers']);
        }
        return $builder->build();
    }
}
