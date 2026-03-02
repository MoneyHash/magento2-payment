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

namespace MoneyHash\Payment\Model\Data;

use Magento\Framework\Exception\NotFoundException;
use MoneyHash\Payment\Api\Data\WebhookInterface;

class Webhook implements WebhookInterface
{
    private const ALLOWED_TYPES = [
        'transaction.capture.successful',
        'transaction.purchase.successful',
        'transaction.authorize.successful'
    ];
    private const CAPTURED_TYPES = [
        'transaction.capture.successful',
        'transaction.purchase.successful'
    ];
    private string $type;
    private string $transactionId;
    private string $intentId;
    private string $incrementId;
    private string $paymentStatus;
    private array $rawData;

    public function __construct(array $data)
    {
        $this->rawData = $data;
        $this->type = $data['type'] ?? '';
        $this->transactionId = $data['transaction']['id'] ?? '';
        $this->incrementId = $data['transaction']['merchant_reference'] ?? '';
        $this->intentId = $data['intent']['id'] ?? null;
        $this->paymentStatus = $data['intent']['payment_status']['status'] ?? '';
        if (!$this->intentId) {
            throw new NotFoundException(__("No Intent Provided"));
        }
    }

    public function isValid(): bool
    {
        return !empty($this->type) &&
            !empty($this->transactionId) &&
            !empty($this->paymentStatus) &&
            $this->isValidType();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getIntentId(): string
    {
        return $this->intentId;
    }

    public function getPaymentStatus(): string
    {
        return $this->paymentStatus;
    }

    public function getRawData(): array
    {
        return $this->rawData;
    }

    public function isValidType(): bool
    {
        return in_array($this->type, self::ALLOWED_TYPES, true);
    }

    public function isCaptured(): bool
    {
        return in_array($this->type, self::CAPTURED_TYPES)  && $this->paymentStatus === 'CAPTURED';
    }

    public function isAuthorized(): bool
    {
        return $this->type === "transaction.authorize.successful" && $this->paymentStatus === 'AUTHORIZED';
    }

    public function getIncrementId(): string
    {
        list($incrementId, ) = explode('_', $this->incrementId);
        return $incrementId;
    }
}
