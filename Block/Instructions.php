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

namespace MoneyHash\Payment\Block;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use MoneyHash\Payment\Model\Ui\ConfigProvider;

class Instructions extends Template
{
    private $_checkoutSession;

    public function __construct(
        Template\Context $context,
        Session $_checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $_checkoutSession;
    }

    /**
     * returns order instructions for Aman and Fawry
     * @return string|null
     */
    public function getInstructions(): ?string
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        if ($order->getId()) {
            $information = $order->getPayment()?->getAdditionalInformation();
            if (!empty($information[ConfigProvider::ORDER_INSTRUCTIONS])) {
                return is_array($information[ConfigProvider::ORDER_INSTRUCTIONS]) ? implode("\n", $information[ConfigProvider::ORDER_INSTRUCTIONS]) : $information[ConfigProvider::ORDER_INSTRUCTIONS];
            }
        }
        return null;
    }
}
