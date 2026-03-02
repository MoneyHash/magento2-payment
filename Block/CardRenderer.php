<?php declare(strict_types=1);
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

namespace MoneyHash\Payment\Block;

use Magento\Vault\Block\AbstractCardRenderer;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use MoneyHash\Payment\Model\Ui\ConfigProvider;


class CardRenderer extends AbstractCardRenderer
{
    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token)
    {
        return $token->getPaymentMethodCode() === ConfigProvider::CODE_ALL;
    }

    /**
     * @return string
     */
    public function getNumberLast4Digits()
    {
        $tokenDetails = $this->getTokenDetails();

        return substr($tokenDetails['number'], -4);
    }

    /**
     * @return string
     */
    public function getExpDate()
    {
        return substr($this->getToken()->getExpiresAt(), 0, 10);
    }

    /**
     * @return string
     */
    public function getIconUrl()
    {
        $card_type = $this->getTokenDetails()['scheme'];
        $m_card_type = $this->convertCardType($card_type);

        return $this->getIconForType($m_card_type)['url'];
    }

    /**
     * @return ?int
     */
    public function getIconHeight()
    {
        return null;
    }

    /**
     * @return ?int
     */
    public function getIconWidth()
    {
        return null;
    }


    /**
     * @param string $card_type
     * @return string
     */
    private function convertCardType(string $card_type): string
    {
        return match ($card_type) {
            'VISA' => 'VI',
            'MASTERCARD' => 'MC',
            'AMERICANEXPRESS' => 'AE',
            'JCB' => 'JCB',
            'Discover' => 'DI',
            default => 'OT',
        };
    }
}
