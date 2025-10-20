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

namespace MoneyHash\Payment\Model\ResourceModel\WebhookTransaction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MoneyHash\Payment\Model\WebhookTransaction;
use MoneyHash\Payment\Model\ResourceModel\WebhookTransaction as ResourceModel;
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(WebhookTransaction::class, ResourceModel::class);
    }
    public function addIntentFilter(string $intent_id)
    {
        $this->addFieldToFilter('intent_id', $intent_id);
        return $this;
    }
}
