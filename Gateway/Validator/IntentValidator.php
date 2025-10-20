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

namespace MoneyHash\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class IntentValidator extends AbstractValidator
{
    public function __construct(
        ResultInterfaceFactory $resultFactory,
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
        $status = false;
        $message = __("Couldn't validate gateway response");

        // Check if status exists and code is 200
        if (isset($response['status']) && isset($response['status']['code']) && $response['status']['code'] == 200) {

            // Check if data exists and has an ID
            if (!empty($response['data']) && isset($response['data']['id'])) {
                $status = true;
                $message = __("Intent successful");
            } else {
                $message = __("Intent data is missing in the response");
            }

        } elseif (!empty($response['status']['errors'])) {
            $message = __("Please check your response");
        }

        return $this->createResult(
            $status,
            [$message]
        );
    }
}
