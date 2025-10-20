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

namespace MoneyHash\Payment\Model;

use Magento\Framework\Model\AbstractModel;
use MoneyHash\Payment\Api\Data\WebhookTransactionInterface;

class WebhookTransaction extends AbstractModel implements WebhookTransactionInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\WebhookTransaction::class);
    }

    /**
     * @inheritDoc
     */
    public function getIntentId(): string
    {
        return $this->_getData(self::INTENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setIntentId(string $intentId): self
    {
        return $this->setData(self::INTENT_ID, $intentId);
    }

    /**
     * @inheritDoc
     */
    public function getQuoteId(): ?int
    {
        return $this->_getData(self::QUOTE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setQuoteId(?int $quoteId): self
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): string
    {
        $payload = $this->getData(self::PAYLOAD);
        if ($payload === null) {
            return '';
        }
        $decoded = @gzdecode($payload);
        return $decoded !== false ? $decoded : '';
    }

    /**
     * @inheritDoc
     */
    public function setPayload(string $payload): self
    {
        $compressed = @gzencode($payload, 9);
        return $this->setData(self::PAYLOAD, $compressed);
    }

    /**
     * @inheritDoc
     */
    public function getIsProcessed(): int
    {
        return $this->_getData(self::IS_PROCESSED);
    }

    /**
     * @inheritDoc
     */
    public function setIsProcessed(int $isProcessed): self
    {
        return $this->setData(self::IS_PROCESSED, $isProcessed);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): string
    {
        return $this->_getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $createdAt): self
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getProcessedAt(): ?string
    {
        return $this->_getData(self::PROCESSED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setProcessedAt(?string $processedAt): self
    {
        return $this->setData(self::PROCESSED_AT, $processedAt);
    }
}
