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

namespace MoneyHash\Payment\Block\Adminhtml\Order\View\Info;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Block\Adminhtml\Order\View\Tab\Info;
use MoneyHash\Payment\Model\Ui\ConfigProvider;

class Card extends Template
{

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order = null;

    /**
     * AchDetails constructor.
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = [])
    {
        $this->escaper = $context->getEscaper();
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order
     * @throws LocalizedException
     */
    public function getOrder()
    {
        if ($this->order === null) {
            /** @var Info $parent */
            $parent = $this->getLayout()->getBlock('order_tab_info');
            $this->order = $parent->getOrder();
        }
        return $this->order;
    }

    /**
     * @return OrderPaymentInterface
     * @throws LocalizedException
     */
    public function getPayment(): OrderPaymentInterface
    {
        return $this->getOrder()->getPayment();
    }

    /**
     * @param array|string $data
     * @param string|null $field
     * @return array|string
     */
    public function safeValue($data, $field = null)
    {
        if ($field === null) {
            return !empty($data) ? $this->escaper->escapeHtml($data) : '-';
        }
        if (is_array($data)) {
            return isset($data[$field]) ? $this->escaper->escapeHtml($data[$field]) : '-';
        }
        return '-';
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function toHtml()
    {
        if ($this->getPayment()->getMethod() !== ConfigProvider::CODE_ALL) {
            return '';
        }
        return parent::toHtml();
    }
}
