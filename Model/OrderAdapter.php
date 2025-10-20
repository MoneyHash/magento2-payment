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

use Magento\Payment\Gateway\Data\Order\AddressAdapterFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order;
use MoneyHash\Payment\Model\Ui\ConfigProvider;

class OrderAdapter extends \Magento\Payment\Gateway\Data\Order\OrderAdapter
{
    private $order;
    private $cartRepository;
    public function __construct(
        Order $order,
        AddressAdapterFactory $addressAdapterFactory,
        CartRepositoryInterface $cartRepository
    )
    {
        $this->order = $order;
        parent::__construct($order, $addressAdapterFactory);
        $this->cartRepository = $cartRepository;
    }

    public function getIntentId()
    {
        if($this->order->getQuoteId()){
            $quote = $this->cartRepository->get($this->order->getQuoteId());
            return $quote->getData(ConfigProvider::INTENT_ID);
        }
        return null;
    }
    public function getCurrencyCodeForStore()
    {
        return $this->order->getStoreCurrencyCode();
    }

    public function getShippingAmount()
    {
        return $this->order->getShippingAmount();
    }
    public function getTaxAmount()
    {
        return $this->order->getTaxAmount();
    }

    public function getSubtotal()
    {
        return $this->order->getSubtotal();
    }
    public function getDiscountAmount()
    {
        return $this->order->getDiscountAmount();
    }
}
