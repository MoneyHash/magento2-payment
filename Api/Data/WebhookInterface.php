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

namespace MoneyHash\Payment\Api\Data;

interface WebhookInterface
{
    /**
     * Check if webhook data is valid
     *
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Get webhook type
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Get transaction ID
     *
     * @return string
     */
    public function getTransactionId(): string;

    /**
     * Get increment ID
     *
     * @return string
     */
    public function getIncrementId(): string;

    /**
     * Get Intent ID
     *
     * @return string
     */
    public function getIntentId(): string;

    /**
     * Get payment status
     *
     * @return string
     */
    public function getPaymentStatus(): string;

    /**
     * Get raw webhook data
     *
     * @return array
     */
    public function getRawData(): array;

    /**
     * Check if this is a successful payment webhook
     *
     * @return bool
     */
    public function isValidType(): bool;

    /**
     * Check if payment is captured
     *
     * @return bool
     */
    public function isCaptured(): bool;

    /**
     * Check if payment is authorized
     *
     * @return bool
     */
    public function isAuthorized(): bool;
}
