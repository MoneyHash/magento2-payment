<?php

namespace MoneyHash\Payment\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Encrypted;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use MoneyHash\Payment\Gateway\Http\Client\Client;
use MoneyHash\Payment\Gateway\Http\TransferFactory;
use MoneyHash\Payment\Gateway\Request\Endpoint;
use MoneyHash\Payment\Gateway\Validator\ApiKeyValidator;

class ApiKey extends Encrypted
{
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        private readonly Client $client,
        private readonly TransferFactory $transferFactory,
        private readonly ApiKeyValidator $apiKeyValidator,
        private readonly StoreManagerInterface $storeManager,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $encryptor,
            $resource,
            $resourceCollection,
            $data
        );
    }
    public function beforeSave()
    {
        $value = (string)$this->getValue();
        // don't save value, if an obscured value was received. This indicates that data was not changed.
        if (!preg_match('/^\*+$/', $value) && !empty($value)) {
            $this->validateKey($value);
        }
        parent::beforeSave();
    }
    private function validateKey($value): void
    {
        $transferObject = $this->transferFactory->create([
            'endpoint' => Endpoint::ACCOUNTS . '/payment_methods',
            'headers' => [
                'content-type' => 'application/json',
                'X-Api-Key' => $value
            ],
            'currency' => $this->storeManager->getStore()->getCurrentCurrencyCode()
        ]);
        $response = $this->client->placeRequest($transferObject);

        $validationResult = $this->apiKeyValidator->validate($response);
        if (!$validationResult->isValid()) {
            throw new \Exception(implode('\n', $validationResult->getFailsDescription()));
        }
    }
}
