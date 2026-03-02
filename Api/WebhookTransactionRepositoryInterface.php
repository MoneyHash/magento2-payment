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

namespace MoneyHash\Payment\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use MoneyHash\Payment\Api\Data\WebhookTransactionInterface;

interface WebhookTransactionRepositoryInterface
{
    /**
     * Save raw webhook transaction data
     *
     * @param string $intentId
     * @param array $payload Serialized (e.g. JSON) payload
     * @return WebhookTransactionInterface
     * @throws LocalizedException
     */
    public function saveRawData(string $intentId, array $payload): WebhookTransactionInterface;

    /**
     * Save  transaction data
     *
     * @param WebhookTransactionInterface $transaction
     * @return WebhookTransactionInterface
     * @throws LocalizedException
     */
    public function save(WebhookTransactionInterface $transaction): WebhookTransactionInterface;

    /**
     * Get Webhook Transaction by Intent ID
     *
     * @param string $intentId
     * @param int $isProcessed Optional filter by is_processed (e.g. 0 for unprocessed)
     * @return WebhookTransactionInterface
     * @throws NoSuchEntityException
     */
    public function getLastTransactionByIntentId(
        string $intentId,
        int $isProcessed = 0
    ): WebhookTransactionInterface;
}
