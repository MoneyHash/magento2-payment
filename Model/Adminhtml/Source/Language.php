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

namespace MoneyHash\Payment\Model\Adminhtml\Source;


class Language implements \Magento\Framework\Data\OptionSourceInterface
{
    const LANG_AR = "ar-EG";
    const LANG_EN = "en-US";
    const LANG_FR = "fr-FR";

    public function toOptionArray()
    {
        return [
            [
                'label' => __("Arabic"),
                'value' => self::LANG_AR
            ],
            [
                'label' => __("English"),
                'value' => self::LANG_EN
            ],
            [
                'label' => __("French"),
                'value' => self::LANG_FR
            ]
        ];
    }
}
