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

namespace MoneyHash\Payment\Gateway\Request\Builder;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use MoneyHash\Payment\Gateway\Validator\PhoneValidator;

class ShippingData implements BuilderInterface
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
     * @throws NoSuchEntityException|LocalizedException
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $shippingAddress = $order->getShippingAddress();

        if ($shippingAddress === null) {
            return [];
        }
        if (!$shippingAddress->getTelephone() || !$this->phoneValidator->validateMobileNumber($shippingAddress->getTelephone(),
                $shippingAddress->getCountryId())) {
            throw new LocalizedException(__('Mobile number is not valid. Please use format %1',
                $this->phoneValidator->getCountryExample($shippingAddress->getCountryId())));
        }
        $country = $this->countryInfo->getCountryInfo($shippingAddress->getCountryId());

        $regionCode = $shippingAddress->getRegionCode();
        if (empty($regionCode)) {
            $regionCode = "Cairo";
        }

        return [
            'shipping_data' => [
                'name' => $shippingAddress->getFirstname() . " " . $shippingAddress->getLastname(),
                'first_name' => $shippingAddress->getFirstname(),
                'last_name' => $shippingAddress->getLastname(),
                'email' => $shippingAddress->getEmail() ?: "test@moenyhash.io",
                'city' => $shippingAddress->getCity(),
                'phone_number' => $this->phoneValidator->formatToE164($shippingAddress->getTelephone(),
                    $shippingAddress->getCountryId()),
                'country' => $country->getId(),
                'postal_code' => $shippingAddress->getPostcode(),
                'state' => $regionCode,
                'address' => $shippingAddress->getStreetLine1(),
                'address1' => $shippingAddress->getStreetLine2() != "" ? $shippingAddress->getStreetLine2() : null

            ]
        ];
    }
}
