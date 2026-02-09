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

namespace MoneyHash\Payment\Gateway\Request\Builder;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class OrderData implements BuilderInterface
{

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws NoSuchEntityException
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $result = [
            'amount' => $order->getGrandTotalAmount(),
            'amount_currency' => $order->getCurrencyCodeForStore(),
            'merchant_reference' => $order->getOrderIncrementId()
        ];
        if ($order->getDiscountAmount()) {
            $result['discount'] = [
                "title" => [
                    "en" => "Discount",
                ],
                "type" => "amount",
                "value" => abs($order->getDiscountAmount()),
            ];
        }
        $fees = [];
        if ($order->getShippingAmount()) {
            $fees[] = [
                "title" => [
                    "en" => "Shipping Fees",
                ],
                "value" => $order->getShippingAmount(),
            ];
        }
        if ($order->getTaxAmount()) {
            $fees[] = [
                "title" => [
                    "en" => "Tax",
                ],
                "value" => $order->getTaxAmount(),
            ];
        }
        if (!empty($fees)) {
            $result['fees'] = $fees;
        }
        return $result;
    }
}
