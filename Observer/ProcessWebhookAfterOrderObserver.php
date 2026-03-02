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

namespace MoneyHash\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use MoneyHash\Payment\Api\Data\WebhookInterfaceFactory;
use MoneyHash\Payment\Api\WebhookTransactionRepositoryInterface;
use MoneyHash\Payment\Model\Service\OrderProcessor;
use MoneyHash\Payment\Model\Ui\ConfigProvider;
use Psr\Log\LoggerInterface;

class ProcessWebhookAfterOrderObserver implements ObserverInterface
{
    public function __construct(
        private readonly WebhookTransactionRepositoryInterface $webhookTransactionRepository,
        private readonly LoggerInterface $logger,
        private readonly OrderProcessor $orderProcessor,
        private readonly WebhookInterfaceFactory $webhookFactory,
        private readonly SerializerInterface $serializer,
        private readonly TimeZoneInterface $timezone
    ) {

    }

    public function execute(Observer $observer): void
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        $intentId = $quote->getData(ConfigProvider::INTENT_ID);

        if (!$intentId) {
            return;
        }

        try {
            $webhook = $this->webhookTransactionRepository->getLastTransactionByIntentId($intentId);
            $webhookData = $this->webhookFactory->create(['data' => $this->serializer->unserialize($webhook->getPayload())]);
            $this->orderProcessor->process($webhookData);
            $webhook->setQuoteId($quote->getId())
                ->setIsProcessed(1)
                ->setProcessedAt($this->timezone->date()->format('Y-m-d H:i:s'));
            $this->webhookTransactionRepository->save($webhook);
        } catch (\Exception $e) {
            $this->logger->error("Order Observer Webhook:: " . $e->getMessage());
        }
    }
}
