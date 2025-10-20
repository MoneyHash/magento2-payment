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

use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use MoneyHash\Payment\Gateway\Validator\PhoneValidator;
use MoneyHash\Payment\Helper\CountryPhoneCodes;

class BillingData implements BuilderInterface
{

    /**
     * BillingDataBuilder constructor.
     * @param CountryInformationAcquirerInterface $countryInfo
     */
    public function __construct(
        private readonly CountryInformationAcquirerInterface $countryInfo,
        private readonly PhoneValidator $phoneValidator
    ) {
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws NoSuchEntityException
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();

        if ($billingAddress === null) {
            return [];
        }
        if (!$billingAddress->getTelephone() || !$this->phoneValidator->validateMobileNumber($billingAddress->getTelephone(),
                $billingAddress->getCountryId())) {
            throw new LocalizedException(__('Mobile number is not valid. Please use format %1',
                $this->phoneValidator->getCountryExample($billingAddress->getCountryId())));
        }
        $country = $this->countryInfo->getCountryInfo($billingAddress->getCountryId());

        $regionCode = $billingAddress->getRegionCode();
        if (empty($regionCode)) {
            $regionCode = "Cairo";
        }

        return [
            'billing_data' => [
                'name' => $billingAddress->getFirstname() . " " . $billingAddress->getLastname(),
                'first_name' => $billingAddress->getFirstname(),
                'last_name' => $billingAddress->getLastname(),
                'email' => $billingAddress->getEmail(),
                'city' => $billingAddress->getCity() ?: "test@moneyhash.com",
                'phone_number' => $this->phoneValidator->formatToE164($billingAddress->getTelephone(),
                    $billingAddress->getCountryId()),
                'country' => $country->getId(),
                'postal_code' => $billingAddress->getPostcode(),
                'state' => $regionCode,
                'address' => $billingAddress->getStreetLine1(),
                'address1' => $billingAddress->getStreetLine2() != "" ? $billingAddress->getStreetLine2() : "no address"

            ]
        ];
    }
}
