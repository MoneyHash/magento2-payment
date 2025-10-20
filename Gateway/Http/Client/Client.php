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

namespace MoneyHash\Payment\Gateway\Http\Client;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Psr\Log\LoggerInterface;

class Client implements ClientInterface
{
    /**
     * @param CurlFactory $clientFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private readonly CurlFactory $clientFactory,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     * @throws LocalizedException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();
        $endpoint = "";
        if (!empty($request['endpoint'])) {
            $endpoint = $request['endpoint'];
            unset($request['endpoint']);
        }
        $type = $transferObject->getMethod();
        $client = $this->clientFactory->create();
        $client->setHeaders($transferObject->getHeaders());
        $gatewayUrl = $transferObject->getUri() . $endpoint . '/';
        $this->logger->info("CLIENT REQUEST :: " . $this->serializer->serialize(["uri" => $gatewayUrl, "type" => $type, "request" => $request]));
        if (strtoupper($type) == "POST") {
            $client->post($gatewayUrl, $this->serializer->serialize($request));
        } elseif (strtoupper($type) == "GET") {
            $gatewayUrl = empty($request) ? $gatewayUrl : $gatewayUrl  . "?" .  http_build_query($request);
            $client->get($gatewayUrl);
        }
        $response = $client->getBody();
        $this->logger->info("CLIENT BODY :: " . $response);
        if (!json_validate($response)) {
            throw new LocalizedException(__("Invalid response from Gateway"));
        }
        return $this->serializer->unserialize($response);
    }
}
