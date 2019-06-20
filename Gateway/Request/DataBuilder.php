<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Twispay\Payments\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;

/**
 * Payment Data Builder
 */
class DataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * @param Config $config
     * @param SubjectReader $subjectReader
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(){

    }

    /**
     * @inheritdoc
     */
    public function build()
    {
        $this->_logger->emergency("twispay_emergency_builder");
        $this->_logger->critical("twispay_critical_builder");
        $this->_logger->alert("twispay_alert_builder");
        $this->_logger->error("twispay_error_builder");
        $this->_logger->warning("twispay_warning_builder");
        $this->_logger->notice("twispay_notice_builder");
        $this->_logger->debug("twispay_debug_builder");
        return [];
    }
}
