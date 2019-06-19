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
        return [];
    }
}
