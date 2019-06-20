<?php

namespace Twispay\Payments\Model;

use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Paypal\Model\Billing\Agreement\MethodInterface;
use Magento\Quote\Api\Data\PaymentMethodInterface;

class SaleModel implements MethodInterface, PaymentMethodInterface{
    /**
     * @var \Magento\Payment\Gateway\Command\CommandPoolInterface
     */
    protected $commandPool;

    /**
     * @var CommandPoolInterface
     */
    public function __construct(CommandPoolInterface $commandPool) {
        $this->commandPool = $commandPool;
    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @api
     */
    public function capture(InfoInterface $payment, $amount)
    {

        $this->_logger->emergency("twispay_emergency_model");
        $this->_logger->critical("twispay_critical_model");
        $this->_logger->alert("twispay_alert_model");
        $this->_logger->error("twispay_error_model");
        $this->_logger->warning("twispay_warning_model");
        $this->_logger->notice("twispay_notice_model");
        $this->_logger->debug("twispay_debug_model");



        /** @var CommandInterface $captureGatewayCommand */
        $captureGatewayCommand = $this->commandPool->get('capture');

        $captureGatewayCommand->execute([
            'payment' => $payment,
            'amount' => $amount
        ]);
    }
     /** CODE */
}
