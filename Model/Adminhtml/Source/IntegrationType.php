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

namespace MoneyHash\Payment\Model\Adminhtml\Source;

class IntegrationType implements \Magento\Framework\Data\OptionSourceInterface
{
    public const TYPE_IFRAME = 'iframe';
    public const TYPE_NATIVE = 'native';

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::TYPE_IFRAME,
                'label' => __('Iframe')
            ],
            [
                'value' => self::TYPE_NATIVE,
                'label' => __('Native')
            ]
        ];
    }
}
