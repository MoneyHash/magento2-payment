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

namespace MoneyHash\Payment\Api\Service;

use Exception;
use Magento\Sales\Api\Data\OrderInterface;
use MoneyHash\Payment\Api\Data\WebhookInterface;

interface PaymentCaptureServiceInterface
{
    /**
     * Capture payment and create invoice
     *
     * @param OrderInterface $order
     * @param WebhookInterface $webhookData
     * @return void
     * @throws Exception
     */
    public function captureAndInvoice(OrderInterface $order, WebhookInterface $webhookData): void;
}
