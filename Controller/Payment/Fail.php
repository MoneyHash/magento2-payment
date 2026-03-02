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

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;

class Fail implements HttpGetActionInterface
{

    public function __construct(
        private readonly RedirectFactory $redirectFactory,
        private readonly ManagerInterface $messageManager,
        private readonly RequestInterface $request
    ) {
    }

    public function execute(): ResultInterface
    {
        $params = $this->request->getParams();
        $requiredParams = ['intent_id', 'transaction_id', 'status'];
        foreach ($requiredParams as $key) {
            if (empty($params[$key])) {
                return $this->_forward('noroute');
            }
        }
        if ($params['status'] !== 'FAILED') {
            return $this->_forward('noroute');
        }
        $errorMessage = $params['response_message'] ?? "Unknown error";
        $this->messageManager->addErrorMessage(
            __('Payment failed: %1', $errorMessage)
        );
        return $this->_forward('checkout/cart');
    }

    private function _forward(string $actionName): ResultInterface
    {
        $resultRedirect = $this->redirectFactory->create();
        $resultRedirect->setPath($actionName);
        return $resultRedirect;
    }

}
