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

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Model\MethodInterface;

class OperationData implements BuilderInterface
{

    /**
     * @var string
     */
    protected $operation;

    /**
     * OperationBuilder constructor.
     * @param string $operation
     */
    public function __construct(string $operation = '')
    {
        $this->operation = $operation;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws LocalizedException
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        if (!$this->operation) {
            $this->operation = $paymentDO->getPayment()->getMethodInstance()->getConfigData('operation') ?? "authorize";
        }
        if ($this->operation === MethodInterface::ACTION_AUTHORIZE_CAPTURE) {
            $this->operation = "purchase";
        }
        return [
            'operation' => $this->operation
        ];
    }
}
