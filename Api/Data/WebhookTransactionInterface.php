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

namespace MoneyHash\Payment\Api\Data;

/**
 * Interface WebhookTransactionInterface
 *
 * @api
 */
interface WebhookTransactionInterface
{
    /**#@+
     * Database column constants
     */
    public const INTENT_ID = 'intent_id';
    public const QUOTE_ID = 'quote_id';
    public const PAYLOAD = 'payload';
    public const IS_PROCESSED = 'is_processed';
    public const CREATED_AT = 'created_at';
    public const PROCESSED_AT = 'processed_at';
    /**#@-*/


    /**
     * Get Intent ID
     *
     * @return string
     */
    public function getIntentId(): string;

    /**
     * Set Intent ID
     *
     * @param string $intentId
     * @return $this
     */
    public function setIntentId(string $intentId): self;

    /**
     * Get Quote ID
     *
     * @return int|null
     */
    public function getQuoteId(): ?int;

    /**
     * Set Quote ID
     *
     * @param int|null $quoteId
     * @return $this
     */
    public function setQuoteId(?int $quoteId): self;

    /**
     * Get Payload (Gzip Compressed)
     *
     * @return string
     */
    public function getPayload(): string;

    /**
     * Set Payload (Gzip Compressed)
     *
     * @param string $payload
     * @return $this
     */
    public function setPayload(string $payload): self;

    /**
     * Get Processed Status
     *
     * @return int
     */
    public function getIsProcessed(): int;

    /**
     * Set Processed Status
     *
     * @param int $isProcessed
     * @return $this
     */
    public function setIsProcessed(int $isProcessed): self;

    /**
     * Get Created At
     *
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt): self;

    /**
     * Get Processed At
     *
     * @return string|null
     */
    public function getProcessedAt(): ?string;

    /**
     * Set Processed At
     *
     * @param string|null $processedAt
     * @return $this
     */
    public function setProcessedAt(?string $processedAt): self;
}
