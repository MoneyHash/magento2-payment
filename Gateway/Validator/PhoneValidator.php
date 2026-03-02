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

namespace MoneyHash\Payment\Gateway\Validator;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Magento\Framework\App\Config\ScopeConfigInterface;

class PhoneValidator
{
    private PhoneNumberUtil $phoneUtil;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
        $this->phoneUtil = PhoneNumberUtil::getInstance();
    }

    /**
     * Validate if number is a mobile number
     */
    public function validateMobileNumber(string $phoneNumber, ?string $countryCode = null): bool
    {
        try {
            if (!$countryCode) {
                $countryCode = $this->getDefaultCountry();
            }

            $numberProto = $this->phoneUtil->parse($phoneNumber, $countryCode);

            return $this->phoneUtil->isValidNumber($numberProto) &&
                $this->phoneUtil->getNumberType($numberProto) === \libphonenumber\PhoneNumberType::MOBILE;

        } catch (NumberParseException $e) {
            return false;
        }
    }

    /**
     * Format phone number to E164 international format
     */
    public function formatToE164(string $phoneNumber, ?string $countryCode = null): string
    {
        try {
            if (!$countryCode) {
                $countryCode = $this->getDefaultCountry();
            }

            $numberProto = $this->phoneUtil->parse($phoneNumber, $countryCode);

            if ($this->phoneUtil->isValidNumber($numberProto)) {
                return $this->phoneUtil->format($numberProto, PhoneNumberFormat::E164);
            }

            return $phoneNumber;

        } catch (NumberParseException $e) {
            return $phoneNumber;
        }
    }
    /**
     * Get country examples for validation messages
     */
    public function getCountryExample(string $countryCode): string
    {
        try {
            $exampleNumber = $this->phoneUtil->getExampleNumber($countryCode);
            return $this->phoneUtil->format($exampleNumber, PhoneNumberFormat::INTERNATIONAL);
        } catch (\Exception $e) {
            return '';
        }
    }
    /**
     * Get default country from store configuration
     */
    private function getDefaultCountry(): string
    {
        return $this->scopeConfig->getValue(
            'general/country/default',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?: 'US';
    }
}
