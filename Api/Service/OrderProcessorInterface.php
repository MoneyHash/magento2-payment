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

namespace MoneyHash\Payment\Api\Service;

use InvalidArgumentException;
use MoneyHash\Payment\Api\Data\WebhookInterface;

interface OrderProcessorInterface
{
    /**
     * Process webhook data for order
     *
     * @param WebhookInterface $webhookData
     * @return void
     * @throws InvalidArgumentException
     */
    public function process(WebhookInterface $webhookData): void;
}
