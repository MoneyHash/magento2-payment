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

class ApiKeyValidator extends AbstractValidator
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
        $status = false;
        $message = __("Wrong Money Hash Api Key Provided");
        // Check if status exists and code is 200
        if (isset($validationSubject['status']) && isset($validationSubject['status']['code']) && $validationSubject['status']['code'] == 200) {
            $status = true;
            $message = "";
        }

        return $this->createResult(
            $status,
            [$message]
        );
    }
}
