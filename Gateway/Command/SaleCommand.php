<?php

namespace Twispay\Gateway\Command;

use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Helper\Formatter;

class SaleCommand implements CommandInterface{

    use Formatter;

    /**
     * SaleCommand constructor.
     * @param Transparent $payflowFacade
     */
    public function __construct() {
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return null|ResultInterface
     */
    public function execute(array $commandSubject)
    {
        $this->_logger->info(sprintf("commandSubject: " . print_r($commandSubject,true)));
        $this->_logger->emergency("twispay_emergency");
        $this->_logger->critical("twispay_critical");
        $this->_logger->alert("twispay_alert");
        $this->_logger->error("twispay_error");
        $this->_logger->warning("twispay_warning");
        $this->_logger->notice("twispay_notice");
        $this->_logger->debug("twispay_debug");

        /** @var double $amount */
        $amount = $commandSubject['amount'];
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $commandSubject['payment'];
        $payment = $paymentDO->getPayment();

        $storeId = $paymentDO->getOrder()->getStoreId();
        $this->payflowFacade->setStore($storeId);

        /** @var \Magento\Vault\Api\Data\PaymentTokenInterface $token */
        $token = $payment->getExtensionAttributes()->getVaultPaymentToken();

        $request = $this->payflowFacade->buildBasicRequest();
        $request->setAmt($this->formatPrice($amount));
        $request->setTrxtype(Transparent::TRXTYPE_SALE);
        $request->setOrigid($token->getGatewayToken());

        $this->payflowFacade->addRequestOrderInfo($request, $payment->getOrder());

        $response = $this->payflowFacade->postRequest($request, $this->payflowFacade->getConfig());
        $this->payflowFacade->processErrors($response);
        $this->payflowFacade->setTransStatus($payment, $response);
    }

}
