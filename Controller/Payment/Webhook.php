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

namespace MoneyHash\Payment\Controller\Payment;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Serialize\SerializerInterface;
use MoneyHash\Payment\Api\Data\WebhookInterfaceFactory;
use MoneyHash\Payment\Api\Service\OrderProcessorInterface;
use MoneyHash\Payment\Api\Service\SignatureValidatorInterface;
use MoneyHash\Payment\Model\Exception\SignatureValidationException;
use Psr\Log\LoggerInterface;

class Webhook implements HttpPostActionInterface, CsrfAwareActionInterface
{
    public function __construct(
        private readonly SignatureValidatorInterface $signatureValidator,
        private readonly OrderProcessorInterface $orderProcessor,
        private readonly WebhookInterfaceFactory $webhookFactory,
        private readonly RequestInterface $request,
        private readonly SerializerInterface $serializer,
        private readonly ResultJsonFactory $responseFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute()
    {
        $result = $this->responseFactory->create();
        $response = [];
        try {
            $payload = $this->request->getContent();
            $this->logger->info("WEBHOOK PAYLOAD::" . $payload);
            $signatureHeader = $_SERVER['HTTP_MONEYHASH_SIGNATURE'] ?? '';
            //$this->signatureValidator->validate($payload, $signatureHeader);
            $webhookData = $this->webhookFactory->create(['data' => $this->serializer->unserialize($payload)]);
            if (!$webhookData->isValid()) {
                throw new \InvalidArgumentException('Invalid webhook data structure');
            }
            $this->orderProcessor->process($webhookData);
            $result->setHttpResponseCode(200);
            $response = ['status' => 'success'];
        } catch (SignatureValidationException $exception) {
            $this->logger->error('MoneyHash Webhook: Invalid signature');
            $result->setHttpResponseCode(401);
            $response = ['error' => 'Invalid signature'];
            return $result;
        } catch (\InvalidArgumentException $exception) {
            $this->logger->error('MoneyHash Webhook Validation Error: ' . $exception->getMessage());
            $result->setHttpResponseCode(400);
            $response = ['error' => $exception->getMessage()];
        } catch (\Exception $e) {
            $this->logger->error('MoneyHash Webhook Error: ' . $e->getMessage());
            $result->setHttpResponseCode(500);
            $response = ['error' => 'Internal server error' . $e->getMessage()];
        }
        $this->logger->info("WEBHOOK RESPONSE::" . $this->serializer->serialize($response));
        //$result->setData($response);
        $result->setHttpResponseCode(201); //force return 201
        return $result;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
