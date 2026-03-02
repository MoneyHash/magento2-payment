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

namespace MoneyHash\Payment\Model\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\OrderFactory;
use MoneyHash\Payment\Api\Data\WebhookInterface;
use MoneyHash\Payment\Api\Service\OrderProcessorInterface;
use MoneyHash\Payment\Api\Service\PaymentCaptureServiceInterface;
use MoneyHash\Payment\Api\WebhookTransactionRepositoryInterface;
use Psr\Log\LoggerInterface;

class OrderProcessor implements OrderProcessorInterface
{

    public function __construct(
        private readonly OrderFactory $orderFactory,
        private readonly PaymentCaptureServiceInterface $paymentCaptureService,
        private readonly LoggerInterface $logger,
        private readonly WebhookTransactionRepositoryInterface $webhookTransactionRepository
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function process(WebhookInterface $webhookData): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId($webhookData->getIncrementId());
        $this->webhookTransactionRepository->saveRawData($webhookData->getIntentId(), $webhookData->getRawData());
        if (!$order->getId()) {
            throw new \InvalidArgumentException(
                "Order not found for ID: {$webhookData->getIncrementId()}"
            );
        }
        if ($webhookData->isAuthorized()) {
            //$this->handleSuccessfulPayment($order, $webhookData); // handle authorization
            return;
        } elseif ($webhookData->isCaptured()) {
            $this->handleCapturedTransaction($order, $webhookData);
        } else {
            $this->handleOtherWebhookTypes($order, $webhookData);
        }
    }
    private function handleCapturedTransaction(\Magento\Sales\Model\Order $order, WebhookInterface $webhookData): void
    {
        $this->paymentCaptureService->captureAndInvoice($order, $webhookData);
        $this->logger->info("MoneyHash Webhook: Order {$order->getIncrementId()} processed successfully");
    }

    private function handleOtherWebhookTypes(\Magento\Sales\Model\Order $order, WebhookInterface $webhookData): void
    {
        // Implement other webhook types (failed, refunded, etc.)
        $this->logger->info("MoneyHash Webhook: Handling {$webhookData->getType()} for order {$order->getIncrementId()}");
    }

}
