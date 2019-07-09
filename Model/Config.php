<?php
namespace Twispay\Payments\Model;

class Config
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfigInterface;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $configInterface
    ) {

        $this->scopeConfigInterface = $configInterface;
    }

    public function getLiveMode(){
      return $this->getConfigValue('live_mode');
    }

    public function getStagingSiteId(){
        return $this->getConfigValue('staging_site_id');
    }

    public function getStagingPrivateId(){
        return $this->getConfigValue('staging_private_id');
    }

    public function getLiveSiteId(){
        return $this->getConfigValue('live_site_id');
    }

    public function getLivePrivateId(){
        return $this->getConfigValue('live_private_id');
    }

    public function isDebugMode()
    {
        return !!$this->getConfigValue('debug');
    }

    public function getRedirectUrl()
    {
        if ($this->isDebugMode()) {
            return $this->getConfigValue('test_redirect_url');
        } else {
            return $this->getConfigValue('live_redirect_url');
        }
    }

    public function getBackUrl()
    {
        return $this->getConfigValue('back_url');
    }

    public function getSuccessPage()
    {
        return $this->getConfigValue('success_page');
    }

    // public function getOrderType()
    // {
    //     return $this->getConfigValue('order_type');
    // }

    // public function getCardTransactionMode()
    // {
    //     return $this->getConfigValue('card_transaction_mode');
    // }

    public function isEmailInvoice()
    {
        return !!$this->getConfigValue('email_invoice');
    }

    private function getConfigValue($value)
    {
        return $this->scopeConfigInterface->getValue('payment/twispay/' . $value);
    }
}
