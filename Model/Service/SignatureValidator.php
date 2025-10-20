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

namespace MoneyHash\Payment\Model\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Store\Model\ScopeInterface;
use MoneyHash\Payment\Api\Service\SignatureValidatorInterface;
use MoneyHash\Payment\Model\Exception\SignatureValidationException;

class SignatureValidator implements SignatureValidatorInterface
{
    private const PAYMENT_MONEYHASH_ALL_SECRET_KEY = 'payment/moneyhash_all/secret_key';
    private const PAYMENT_MONEYHASH_ALL_API_KEY = 'payment/moneyhash_all/api_key';
    private const PAYMENT_MONEYHASH_ALL_TEST_MODE = 'payment/moneyhash_all/test_mode';
    private const SIGNATURE_BASE_URL = 'https://web.moneyhash.io/api/v1/organizations/get-webhook-signature-key/';
    private const SIGNATURE_STAGING_URL = 'https://staging-web.moneyhash.io/api/v1/organizations/get-webhook-signature-key/';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly WriterInterface $configWriter,
        private readonly CurlFactory $curlFactory
    ) {
    }

    /**
     * @inheriDoc
     */
    public function validate(string $payload, string $signature): void
    {
        try{
            if (empty($payload) || empty($signature)) {
                throw new LocalizedException(__('Signature verification failed'));
            }
            preg_match('/t=(\d+)/', $signature, $timeMatch);
            preg_match('/v3=([a-f0-9]+)/', $signature, $v3Match);

            $timestamp = $timeMatch[1] ?? null;
            $v3Signature = $v3Match[1] ?? null;
            if (!$timestamp || !$v3Signature) {
                throw new LocalizedException(__('Signature verification failed'));
            }
            $encodedPayload = base64_encode($payload);
            $toSign = $encodedPayload . $timestamp;
            $calculatedSignature = hash_hmac('sha256', $toSign, $this->getSecretKey());

            if(!hash_equals($calculatedSignature, $v3Signature)){
                throw new LocalizedException(__('Signature verification failed'));
            }
        }catch (\Exception $e){
            throw new SignatureValidationException($e->getMessage());
        }
    }

    private function getSecretKey(): string
    {
        $secretKey = $this->scopeConfig->getValue(
            self::PAYMENT_MONEYHASH_ALL_SECRET_KEY,
        );
        if (!$secretKey) {

            $apiKey = $this->scopeConfig->getValue(self::PAYMENT_MONEYHASH_ALL_API_KEY, ScopeInterface::SCOPE_STORE);
            $isTestMode = $this->scopeConfig->isSetFlag(self::PAYMENT_MONEYHASH_ALL_TEST_MODE,
                ScopeInterface::SCOPE_STORE);
            $gatewayUrl = $isTestMode ? self::SIGNATURE_STAGING_URL : self::SIGNATURE_BASE_URL;
            $client = $this->curlFactory->create();
            $client->setHeaders([
                'Content-Type' => 'application/json',
                'X-Api-Key' => $apiKey,
            ]);
            $client->get($gatewayUrl);
            $response = json_decode($client->getBody(), true);

            if (!isset($response['data']) || !isset($response['data']['webhook_signature_secret'])) {
                throw new \Exception('Unable to retrieve MoneyHash secret key from API.');
            }

            $secretKey = $response['data']['webhook_signature_secret'];

            $this->configWriter->save(
                self::PAYMENT_MONEYHASH_ALL_SECRET_KEY,
                $secretKey,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        }
        return $secretKey;
    }
    private function ksortRecursive(&$array)
    {
        if (is_array($array)) {
            ksort($array);
            foreach ($array as &$value) {
                if (is_array($value)) {
                    $this->ksortRecursive($value);
                }
            }
        }
    }
}
