<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Twispay\Payments\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Payment Details Handler
 */
class PaymentDetailsHandler implements HandlerInterface
{
    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(){

    }

    /**
     * @inheritdoc
     */
    public function handle()
    {

    }
}
