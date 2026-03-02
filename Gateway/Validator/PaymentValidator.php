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

namespace MoneyHash\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use MoneyHash\Payment\Model\Ui\ConfigProvider;

class PaymentValidator extends AbstractValidator
{

    public const PROCESSED = ['PROCESSED', 'INTENT_PROCESSED'];
    public const UNPROCESSED = 'UNPROCESSED';

    public function __construct(
        ResultInterfaceFactory $resultFactory
    ) {
        parent::__construct($resultFactory);
    }

    /**
     * Performs validation of result code
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        $response = $validationSubject['response'];
        $isValid = false;
        $messages = [];

        $statusCode = $response['status']['code'] ?? null;
        $statusErrors = $response['status']['errors'] ?? [];

        if ($statusCode !== 200) {
            $messages = $this->flattenErrors($statusErrors);
            if (empty($messages)) {
                $messages[] = __("Gateway returned non-200 response code: %1", $statusCode ?? 'null');
            }
            return $this->createResult(false, $messages);
        }

        if (!empty($statusErrors)) {
            $messages = $this->flattenErrors($statusErrors);
            return $this->createResult(false, $messages);
        }

        $data = !empty($response['data']['intent']) ? $response['data']['intent'] : null;
        if (!$data || !is_array($data)) {
            $messages[] = __("Payment data is missing or invalid in gateway response.");
            return $this->createResult(false, $messages);
        }
        $state = strtoupper((string)($data['status'] ?? ''));
        if (in_array($state, self::PROCESSED)) {
            $isValid = true;
            $messages[] = __("Payment validated successfully.");
        } elseif ($state == self::UNPROCESSED) {
            if (!empty($data['active_transaction']['provider_transaction_fields'])) {
                $isValid = true;
            }
            $messages[] = __("Payment is still unprocessed.");
        }
        return $this->createResult((bool)$isValid, $messages);
    }

    private function flattenErrors(array $errors): array
    {
        $out = [];
        foreach ($errors as $error) {
            if (is_string($error)) {
                $out[] = $error;
                continue;
            }
            if (is_array($error)) {
                foreach ($error as $key => $val) {
                    if (is_array($val)) {
                        $val = implode(', ', array_map('strval', $val));
                    }
                    // If key is numeric, don't include key name
                    if (is_numeric($key)) {
                        $out[] = (string)$val;
                    } else {
                        $out[] = $key . ': ' . (string)$val;
                    }
                }
            } else {
                $out[] = (string)$error;
            }
        }
        return $out;
    }
}
