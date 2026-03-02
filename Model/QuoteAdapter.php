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

namespace MoneyHash\Payment\Model;

use Magento\Payment\Gateway\Data\Quote\AddressAdapterFactory;
use Magento\Quote\Api\Data\CartInterface;
use MoneyHash\Payment\Model\Ui\ConfigProvider;

class QuoteAdapter extends \Magento\Payment\Gateway\Data\Quote\QuoteAdapter
{
    private $quote;

    /**
     * @param CartInterface $quote
     * @param AddressAdapterFactory $addressAdapterFactory
     */
    public function __construct(
        CartInterface $quote,
        AddressAdapterFactory $addressAdapterFactory
    ) {
        $this->quote = $quote;
        parent::__construct($quote, $addressAdapterFactory);
    }
    public function getSubtotal()
    {
        return $this->quote->getSubtotal();
    }
    public function getGrandTotalAmount()
    {
        return $this->quote->getGrandTotal();
    }
    public function getOrderIncrementId()
    {
        $incrementId =  parent::getOrderIncrementId();
        if (!$incrementId) {
            $this->quote->reserveOrderId();
            $incrementId = $this->quote->getReservedOrderId();
        }
        return $incrementId;
    }
    public function getCurrencyCodeForStore()
    {
        return $this->quote->getCurrency()->getQuoteCurrencyCode();
    }
    public function setIntentId(?string $intentId = null)
    {
        $this->quote->setData(ConfigProvider::INTENT_ID, $intentId);
    }
    public function getIntentId()
    {
        $this->quote->getData(ConfigProvider::INTENT_ID);
    }
    public function getShippingAmount()
    {
        return $this->quote->getShippingAddress()->getShippingAmount();
    }
    public function getTaxAmount()
    {
        return $this->quote->getShippingAddress()->getTaxAmount();
    }
    public function getDiscountAmount()
    {
        return $this->quote->getShippingAddress()->getDiscountAmount();
    }
}
