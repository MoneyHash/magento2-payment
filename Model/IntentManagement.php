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
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use MoneyHash\Payment\Api\IntentManagementInterface;
use MoneyHash\Payment\Model\Ui\ConfigProvider;

class IntentManagement implements IntentManagementInterface
{
    public const CREATE_INTENT = 'create_intent';


    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param GuestCartRepositoryInterface $cartRepository
     */
    public function __construct(
        private readonly CommandPoolInterface $commandPool,
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly PaymentDataObjectFactory $paymentDataObjectFactory,
        private readonly GuestCartRepositoryInterface $cartRepository
    ) {
    }


    /**
     * @throws NotFoundException
     * @throws NoSuchEntityException
     * @throws CommandException
     * @throws LocalizedException
     */
    public function createNewIntent(
        string $cartId,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ): array {
        $cartId = (int)$cartId;

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->get($cartId);

        $quote->getPayment()->setQuote($quote);
        $quote->getPayment()->importData(
            $paymentMethod->getData()
        );
        $this->commandPool
            ->get(static::CREATE_INTENT)
            ->execute([
                'payment' => $this->paymentDataObjectFactory->create($quote->getPayment())
            ]);

        $this->quoteRepository->save($quote);
        $intentId = $quote->getData(ConfigProvider::INTENT_ID);

        return [
            'id' => (string)$intentId
        ];
    }

    public function createNewGuestIntent(
        string $cartId,
        string $email,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ): array {
        $quote = $this->cartRepository->get($cartId);
        $quote->getBillingAddress()->setEmail($email);
        $quote->getShippingAddress()->setEmail($email);
        return $this->createNewIntent((string)$quote->getId(), $paymentMethod, $billingAddress);
    }
}
