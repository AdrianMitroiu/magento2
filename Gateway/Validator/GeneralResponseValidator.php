<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Twispay\Payments\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class GeneralResponseValidator extends AbstractValidator
{
    /**
     * Constructor
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     * @param ErrorCodeProvider $errorCodeProvider
     */
    public function __construct() {
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getResponseValidators()
    {
        return [];
    }
}
