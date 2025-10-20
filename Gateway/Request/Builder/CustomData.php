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

use Magento\Eav\Model\Entity\VersionControl\Metadata;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Model\MethodInterface;
use MoneyHash\Payment\Model\Adminhtml\Source\IntegrationType;

class CustomData implements BuilderInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ProductMetadataInterface $productMetadata,
        private readonly ModuleListInterface $moduleList,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {

        return [
            'custom_fields' => $this->getCustomData(),
            'language' => $this->scopeConfig->getValue("payment/moneyhash_all/language") ?? "ar-EG",
            'form_only' => $this->scopeConfig->getValue("payment/moneyhash_all/integration_type") === IntegrationType::TYPE_IFRAME
        ];
    }

    private function getCustomData(): array
    {
        return [
            'source' => 'Magento',
            'version' => $this->productMetadata->getVersion(),
            'app_version' => $this->getModuleVersion()
        ];
    }

    private function getModuleVersion(): string
    {
        $module = $this->moduleList->getOne("MoneyHash_Payment");
        return $module['setup_version'] ?? 'unknown';
    }
}
