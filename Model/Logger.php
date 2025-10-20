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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Driver\File as FileSystem;
use Magento\Framework\Logger\Handler\Base;
use Monolog\JsonSerializableDateTimeImmutable;
use Monolog\Level;
use Psr\Log\LoggerInterface;

class Logger extends \Monolog\Logger implements LoggerInterface
{
    private const FILE_PATH =  BP  . '/var/log/moneyhash/';
    private const IS_LOG_ENABLED_CONFIG_PATH = "payment/moneyhash_all/log_enabled";
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        ?string $fileName = "webhook_%s.log"
    )
    {
        $fileName = sprintf($fileName, date('Y-m-d'));
        $handler = new Base(new FileSystem(), self::FILE_PATH, $fileName);
        parent::__construct('MONEYHASH', [$handler]);
    }
    private function isLogEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::IS_LOG_ENABLED_CONFIG_PATH);
    }
    public function addRecord(
        int|Level $level,
        string $message,
        array $context = [],
        ?JsonSerializableDateTimeImmutable $datetime = null
    ): bool {
        if(!$this->isLogEnabled()) {
            return true;
        }
        return parent::addRecord($level, $message, $context, $datetime);
    }
}
