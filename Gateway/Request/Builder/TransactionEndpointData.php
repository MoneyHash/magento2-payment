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

namespace MoneyHash\Payment\Gateway\Request\Builder;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;
use MoneyHash\Payment\Gateway\Request\Endpoint;
use Psr\Log\LoggerInterface;

class TransactionEndpointData implements BuilderInterface
{
    public const VOID_COMMAND = "void";
    public const CAPTURE_COMMAND = "capture";
    public const REFUND_COMMAND = "refund";
    public function __construct(
        private readonly TransactionRepository $transactionRepository,
        private readonly LoggerInterface $logger,
        private readonly ?string $command = 'void'
    ) {
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $transactionId = $this->findAuthorizationTransactionId($payment->getId()) ?? $payment->getTransactionId();
        return [
            'endpoint' => Endpoint::PAYMENTS . '/' . Endpoint::TRANSACTIONS . '/' . $transactionId . '/' . $this->command,
        ];
    }

    private function findAuthorizationTransactionId(int $paymentId): ?string
    {
        try {
            // use type auth for void and capture commands
            $type = TransactionInterface::TYPE_AUTH;
            if ($this->command === self::REFUND_COMMAND) {
                $type = TransactionInterface::TYPE_CAPTURE; // retrieve capture for refund
            }
            $transaction = $this->transactionRepository->getByTransactionType($type, $paymentId);
            return $transaction->getTxnId();

        } catch (\Exception $e) {
            $this->logger->error("MoneyHash: Error finding authorization transaction - " . $e->getMessage());
            return null;
        }
    }
}
