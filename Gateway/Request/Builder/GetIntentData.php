<?php

namespace MoneyHash\Payment\Gateway\Request\Builder;

use Magento\Payment\Gateway\Helper\SubjectReader;
use MoneyHash\Payment\Gateway\Request\Endpoint;

class GetIntentData extends EndpointData
{

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        return [
            'endpoint' => $this->path . '/' . Endpoint::INTENT . '/' . $paymentDO->getOrder()->getIntentId()
        ];
    }
}
