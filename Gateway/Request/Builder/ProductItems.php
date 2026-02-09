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

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item;

class ProductItems implements BuilderInterface
{
    public function __construct(
        private readonly PriceCurrencyInterface $priceCurrency
    ) {
    }

    /**
     * @param OrderItemInterface[]|null $items
     * @return array
     */
    protected function getOrderItems(?array $items): array
    {
        $result = [];

        /** @var Item $item */
        foreach ($items as $item) {
            if ($item->getChildrenItems()) {
                $result = array_merge($result, $this->getOrderItems($item->getChildrenItems()));
            } else {
                $qty = $item->getQtyOrdered() ?: $item->getQty();
                $lineItem = [
                    'name' => $item->getName(),
                    'description' => $item->getDescription() ?: "No description provided",
                    'sku' => $item->getSku(),
                    'category' => "Electronics",
                    'subcategory' => "Components",
                    'amount' => sprintf('%.2F', $this->convert($item->getBasePrice())),
                    'quantity' => $qty,
                    'type' => $item->getWeight() ? "Physical" : "Digital",
                    'tax' => sprintf('%.2F', $this->convert($item->getBaseTaxAmount()) ?: 1)
                ];
                $discount = $item->getDiscountAmount() ?: $item->getOriginalPrice() - $item->getPrice();
                if ($discount) {
                    $lineItem['discount'] = [
                        "title" => [
                            "en" => "Discount",
                        ],
                        "type" => "amount",
                        "value" => abs($discount),
                    ];
                }
                $result[] = $lineItem;
            }
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        return [
            'product_items' => $this->getOrderItems($order->getItems())
        ];
    }
    private function convert($amount): float
    {
        return $this->priceCurrency->convert($amount);
    }
}
