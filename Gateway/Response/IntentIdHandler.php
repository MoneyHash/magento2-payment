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

namespace MoneyHash\Payment\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use MoneyHash\Payment\Model\Ui\ConfigProvider;

class IntentIdHandler implements HandlerInterface
{

    /**
     * Handles transaction id
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (
            !isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDO = $handlingSubject['payment'];
        $intentId = $response['data']['id'];
        $paymentDO->getOrder()->setIntentId($intentId);
    }
}
