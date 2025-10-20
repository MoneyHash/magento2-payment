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

namespace MoneyHash\Payment\Controller\Payment;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Api\Data\PaymentInterfaceFactory;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use MoneyHash\Payment\Model\Ui\ConfigProvider;

class Success implements HttpGetActionInterface
{

    public function __construct(
        private readonly RequestInterface $request,
        private readonly RedirectFactory $redirectFactory,
        private readonly ManagerInterface $messageManager,
        private readonly CheckoutSession $checkoutSession,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly CartManagementInterface $cartManagement,
        private readonly CollectionFactory $quoteCollectionFactory
    ) {
    }

    public function execute(): \Magento\Framework\Controller\ResultInterface
    {

 //       try {
            $params = $this->request->getParams();
            if (empty($params['intent_id'])) {
                throw new \Exception(__('Invalid response from gateway.'));
            }
            $quote = $this->getQuote($params['intent_id']);
            if (!$quote) {
                throw new \Exception(__('No active quote found.'));
            }
            $orderId = $this->cartManagement->placeOrder($quote->getId());
            if (!$orderId) {
                throw new \Exception(__('Order could not be created.'));
            }
            $order = $this->orderRepository->get($orderId);
            $this->checkoutSession->setLastQuoteId($quote->getId());
            $this->checkoutSession->setLastSuccessQuoteId($quote->getId());
            $this->checkoutSession->setLastOrderId($orderId);
            $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
            $resultRedirect = $this->redirectFactory->create();
            $resultRedirect->setPath('checkout/onepage/success');
            return $resultRedirect;

/*        } catch (\Exception $e) {
            $this->checkoutSession->restoreQuote();
            $this->messageManager->addErrorMessage(
                __('Payment failed: %1', $e->getMessage())
            );

            $resultRedirect = $this->redirectFactory->create();
            $resultRedirect->setPath('checkout/cart');
            return $resultRedirect;
        }*/
    }
    private function getQuote($intentId){
        $collection = $this->quoteCollectionFactory->create();
        $collection->addFieldToFilter(ConfigProvider::INTENT_ID, $intentId)
            ->load();
        if($collection->getSize()){
            return $collection->getFirstItem();
        }
        return null;
    }
}
