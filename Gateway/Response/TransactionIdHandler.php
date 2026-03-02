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

namespace MoneyHash\Payment\Gateway\Response;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use MoneyHash\Payment\Model\Ui\ConfigProvider;

class TransactionIdHandler implements HandlerInterface
{

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
        $data = !empty($response['data']['intent']) ? $response['data']['intent'] : null;
        if (!empty($data['active_transaction'])) {
            $transaction = $data['active_transaction'];
            $payment->setAdditionalInformation('method_title', $transaction['payment_method']);
            if (strcasecmp($transaction['status'], 'SUCCESSFUL') === 0) {
                $payment->setTransactionId($transaction['id']);
                if (strtolower($transaction['payment_method']) === "card") {
                    $cardDetails = $transaction['payment_method_details']['data'];
                    if (!empty($cardDetails)) {
                        $payment->setAdditionalInformation('card_scheme', static::safeValue($cardDetails, 'brand'));
                        $payment->setAdditionalInformation(
                            'card_number',
                            'XXXX-' . $cardDetails['last_4']
                        );
                        $payment->setAdditionalInformation(
                            'card_expiry_date',
                            sprintf(
                                '%s/%s',
                                $cardDetails['expiry_month'],
                                $cardDetails['expiry_year']
                            )
                        );
                        if (isset($cardDetails['funding_method'])) {
                            $payment->setAdditionalInformation(
                                'fundingMethod',
                                static::safeValue($cardDetails, 'funding_method')
                            );
                        }
                        if (isset($cardDetails['issuer'])) {
                            $payment->setAdditionalInformation('issuer', static::safeValue($cardDetails, 'issuer'));
                        }
                        if (isset($cardDetails['card_holder_name'])) {
                            $payment->setAdditionalInformation(
                                'nameOnCard',
                                static::safeValue($cardDetails, 'card_holder_name')
                            );
                        }
                    }
                }
                $payment->setIsTransactionClosed(true);
            } elseif (strcasecmp($transaction['status'], 'PENDING_EXTERNAL_ACTION') === 0) {
                $payment->setTransactionId($data['active_transaction']['id']);
                $payment->setAdditionalInformation(
                    ConfigProvider::ORDER_INSTRUCTIONS,
                    $data['active_transaction']['external_action_message'] ?? null
                );
                $payment->setAdditionalInformation(
                    ConfigProvider::PROVIDER_TRANSACTION_FIELDS,
                    $data['active_transaction']['provider_transaction_fields'] ?? null
                );
                $payment->setIsTransactionPending(true);
            }
        }
    }

    public static function safeValue($data, $field)
    {
        return $data[$field] ?? null;
    }
}
