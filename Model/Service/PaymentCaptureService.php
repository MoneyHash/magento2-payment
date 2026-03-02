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

use Magento\Framework\DB\Transaction as DbTransaction;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Service\InvoiceService;
use MoneyHash\Payment\Api\Data\WebhookInterface;
use MoneyHash\Payment\Api\Service\PaymentCaptureServiceInterface;
use Psr\Log\LoggerInterface;

class PaymentCaptureService implements PaymentCaptureServiceInterface
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly DbTransaction $dbTransaction,
        private readonly BuilderInterface $transactionBuilder,
        private readonly LoggerInterface $logger,
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    public function captureAndInvoice(OrderInterface $order, WebhookInterface $webhookData): void
    {
        if ($order->hasInvoices()) {
            $this->logger->info("MoneyHash: Invoice already exists for order {$order->getIncrementId()}");
            return;
        }

        try {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE)
                ->setTransactionId($webhookData->getTransactionId())
                ->register();
            $invoice->pay()->save();
            $this->dbTransaction->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();

            $this->createTransaction($order, $webhookData);

            $order->setState(Order::STATE_PROCESSING)
                ->setStatus(Order::STATE_PROCESSING);
            $this->addCaptureComment($order, $webhookData);
            $this->orderRepository->save($order);

            $this->logger->info("MoneyHash: Invoice created for order {$order->getIncrementId()}");

        } catch (\Exception $e) {
            $this->logger->error("MoneyHash: Error creating invoice - " . $e->getMessage());
            throw $e;
        }
    }

    private function createTransaction(OrderInterface $order, WebhookInterface $webhookData): void
    {
        $payment = $order->getPayment();
        $payment->setAmountPaid($order->getGrandTotal())
            ->setBaseAmountPaid($order->getBaseGrandTotal())
            ->setShouldCloseParentTransaction(true);

        $this->transactionBuilder->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($webhookData->getTransactionId())
            ->setAdditionalInformation([
                Order\Payment\Transaction::RAW_DETAILS => $this->getPaymentAdditionalInfo($webhookData)
            ])
            ->setFailSafe(true)
            ->build(TransactionInterface::TYPE_CAPTURE);
    }

    public function addCaptureComment(OrderInterface $order, WebhookInterface $webhookData): void
    {
        try {
            $transactionData = $webhookData->getRawData()['transaction'] ?? [];
            $operationData = $transactionData['operations'][0] ?? [];

            $comment = __(
                'Payment captured via MoneyHash. Transaction ID: %1. Authorization Code: %2. Amount: %3 %4.',
                $webhookData->getTransactionId(),
                $operationData['authorization_code'] ?? 'N/A',
                $operationData['amount']['value'] ?? $transactionData['amount']['value'] ?? 0,
                $operationData['amount']['currency'] ?? $transactionData['amount']['currency'] ?? ''
            );

            $order->addCommentToStatusHistory($comment, true)
                ->setIsCustomerNotified(true);

            $this->logger->info("MoneyHash: Capture comment added to order {$order->getIncrementId()}");

        } catch (\Exception $e) {
            $this->logger->error("MoneyHash: Error adding capture comment - " . $e->getMessage());
            $fallbackComment = __('Payment captured via MoneyHash. Transaction ID: %1',
                $webhookData->getTransactionId());
            $order->addCommentToStatusHistory($fallbackComment, true);
        }
    }
    private function getPaymentAdditionalInfo(WebhookInterface $webhookData): array
    {
        $rawData = $webhookData->getRawData();
        $transactionData = $rawData['transaction'] ?? [];

        return [
            'operation_id' => $rawData['operation_id'] ?? '',
            'authorization_code' => $transactionData['authorization_code'] ?? '',
            'payment_method' => $transactionData['method']['display_name'] ?? '',
            'card_last4' => $transactionData['payment_method_details']['data']['last_4'] ?? '',
            'provider_reference' => $transactionData['provider_unique_reference']['value'] ?? ''
        ];
    }
}
