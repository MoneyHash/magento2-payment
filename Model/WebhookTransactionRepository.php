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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use MoneyHash\Payment\Api\Data\WebhookTransactionInterface;
use MoneyHash\Payment\Api\Data\WebhookTransactionInterfaceFactory;
use MoneyHash\Payment\Api\WebhookTransactionRepositoryInterface;
use MoneyHash\Payment\Model\ResourceModel\WebhookTransaction as ResourceModel;
use MoneyHash\Payment\Model\ResourceModel\WebhookTransaction\CollectionFactory;
use MoneyHash\Payment\Model\ResourceModel\WebhookTransaction\Collection;
class WebhookTransactionRepository implements WebhookTransactionRepositoryInterface
{
    public function __construct(
        private readonly ResourceModel $resource,
        private readonly CollectionFactory $collectionFactory,
        private readonly WebhookTransactionInterfaceFactory $transactionFactory,
        private readonly TimezoneInterface $timezone,
        private readonly SerializerInterface $serializer
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function save(WebhookTransactionInterface $transaction): WebhookTransactionInterface
    {
        try {
            $this->resource->save($transaction);
        } catch (\Exception $e) {
            throw new LocalizedException(__('Could not save webhook transaction: %1', $e->getMessage()));
        }
        return $transaction;
    }
    /**
     * @inheritdoc
     */
    public function saveRawData(string $intentId, array $payload): WebhookTransactionInterface
    {
        try {
            $transaction = $this->transactionFactory->create();
            $transaction->setIntentId($intentId)
                ->setPayload($this->serializer->serialize($payload))
                ->setIsProcessed(0)
                ->setCreatedAt($this->timezone->date()->format('Y-m-d H:i:s'));
            $this->resource->save($transaction);
            return $transaction;
        } catch (\Exception $e) {
            throw new LocalizedException(__('Could not save webhook transaction: %1', $e->getMessage()));
        }
    }


    /**
     * @inheritDoc
     */
    public function getLastTransactionByIntentId(
        string $intentId,
        int $isProcessed = 0
    ): WebhookTransactionInterface {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addIntentFilter($intentId)
            ->addFieldToFilter(WebhookTransactionInterface::IS_PROCESSED, $isProcessed)
            ->setOrder(WebhookTransactionInterface::CREATED_AT, 'DESC')
            ->setPageSize(1);
        if(!$collection->getSize()) {
            throw new NoSuchEntityException(__('Webhook transaction with intent ID "%1" not found.', $intentId));
        }
        return $collection->getFirstItem();
    }
}
