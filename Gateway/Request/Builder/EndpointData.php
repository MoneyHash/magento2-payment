<?php
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

namespace MoneyHash\Payment\Gateway\Request\Builder;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use MoneyHash\Payment\Gateway\Request\Endpoint;

class EndpointData implements BuilderInterface
{

    /**
     * @param string $endpoint
     */
    public function __construct(
        private string $endpoint = '',
        protected readonly string $path = Endpoint::PAYMENTS
    ) {
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws LocalizedException
     */
    public function build(array $buildSubject)
    {

        return [
            'endpoint' => $this->path . '/' . $this->endpoint
        ];
    }
}
