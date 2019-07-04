<?php

namespace Twispay\Payments\Helper;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Framework\Exception\PaymentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;

/**
 * Helper class for everything that has to do with payment
 *
 * @package Twispay\Payments\Helper
 */
class Payment extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Store manager object
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Twispay\Payments\Logger\Logger
     */
    private $log;

    /**
     * @var \Twispay\Payments\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Twispay\Payments\Model\Config $config
     * @param \Twispay\Payments\Logger\Logger $twispayLogger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Twispay\Payments\Model\Config $config,
        \Twispay\Payments\Logger\Logger $twispayLogger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->log = $twispayLogger;
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;

        $this->objectManager = ObjectManager::getInstance();
    }

    public function getBackUrl()
    {
        $backUrl = $this->config->getBackUrl();
        if (isset($backUrl) && trim($backUrl)!=='') {
            return $this->storeManager->getStore()->getBaseUrl() . $this->config->getBackUrl();
        }

        return "";
    }

    /**
     * This method computes the checksum on the given data array
     *
     * @param array $data
     * @return string the computed checksum
     */
    // public function computeChecksum(array &$data)
    // {
    //     // Get the API key from the cache to be used as an encryption key
    //     $apiKey = $this->config->getApiKey();

    //     // Sort the keys in the object alphabetically
    //     $this->recursiveKeySort($data);

    //     $this->log->debug(var_export($data, true));

    //     // Build an encoded HTTP query string from the data
    //     $query = http_build_query($data);

    //     $this->log->debug($query);

    //     // Encrypt the query string with SHA-512 algorithm
    //     $encoded = hash_hmac('sha512', $query, $apiKey, true);

    //     $checksum = base64_encode($encoded);

    //     $this->log->debug("Checksum: " . $checksum);

    //     return $checksum;
    // }

    /**
     * Get the `jsonRequest` parameter (order parameters as JSON and base64 encoded).
     *
     * @param array $orderData The order parameters.
     *
     * @return string
     */
    public static function getBase64JsonRequest(array $orderData){
        return base64_encode(json_encode($orderData));
    }

    /**
     * Get the `checksum` parameter (the checksum computed over the `jsonRequest` and base64 encoded).
     *
     * @param array $orderData The order parameters.
     * @param string $secretKey The secret key (from Twispay).
     *
     * @return string
     */
    public static function getBase64Checksum(array $orderData, $secretKey){
        $hmacSha512 = hash_hmac(/*algo*/'sha512', json_encode($orderData), $secretKey, /*raw_output*/true);
        return base64_encode($hmacSha512);
    }


    /**
     * Sort the array based on the keys
     * @param array $data
     */
    private function recursiveKeySort(array &$data)
    {
        ksort($data, SORT_STRING);
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->recursiveKeySort($data[$key]);
            }
        }
    }

    /**
     * Prepares the request data to be sent to the Twispay gateway
     *
     * @param $orderId
     * @param $isGuestCustomer
     *
     * @return array $data
     */
    public function prepareGatewayRequest($orderId, $isGuestCustomer)
    {
        /** NEW IMPLEMENTATION STARTS */
        // Get the details of the last order
        /** @var \Magento\Sales\Model\Order $order */
        $this->log->info('prepare Gateway Request order ID: ', [$orderId]);
        $order = $this->orderRepository->get($orderId);

        // Set the status of this order to pending payment
        $order->setState(Order::STATE_PENDING_PAYMENT, true);
        $order->setStatus(Order::STATE_PENDING_PAYMENT);
        $order->addStatusToHistory($order->getStatus(), __('Redirecting to Twispay payment gateway'));
        $order->save();

        //$this->log->info('prepareGatewayRequest order: ', [print_r($order->debug(), TRUE)]);

        /** Get billing details. */
        $billingAddress = $order->getBillingAddress();
        $shippingAdress = $order->getShippingAddress();

        /** Get the Site ID and Private Key depending on Live Mode value. */
        $siteId = '';
        $secretKey = '';
        if( 1 == $this->config->getLiveMode()){
            $siteId = $this->config->getLiveSiteId();
            $this->log->info('prepareGatewayRequest live site ID: ', [$siteId]);
            $secretKey = $this->config->getLivePrivateId();
            $this->log->info('prepareGatewayRequest live secret key: ', [$secretKey]);
        } elseif( 0 == $this->config->getLiveMode() ) {
            $siteId = $this->config->getStagingSiteId();
            $this->log->info('prepareGatewayRequest staging site ID: ', [$siteId]);
            $secretKey = $this->config->getStagingPrivateId();
            $this->log->info('prepareGatewayRequest staging secret key: ', [$secretKey]);
        } else {
            /** TODO error */
        }

        /** Get customer details. */
        $customer = [
            'identifier' => ( $isGuestCustomer ) ? ( '_' .$orderId . '_' . date('YmdHis') ) : ( '_' . $billingAddress->getCustomerId() ),
            'firstName' => ( $billingAddress->getFirstname() != null ) ? ( $billingAddress->getFirstname() ) : ( $shippingAdress->getFirstname() ),
            'lastName' => ( $billingAddress->getLastname() != null ) ? ( $billingAddress->getLastname() ) : ( $shippingAdress->getLastname() ),
            'country' => ( $billingAddress->getCountryId() != null ) ? ( $billingAddress->getCountryId() ) : ( $shippingAdress->getCountryId() ),
            /** 'state' => ( ($billingAddress->getCountryId() == 'US') && ($billingAddress->getRegionCode() != null) ) ? ( $billingAddress->getRegionCode() ) : ( $shippingAdress->getRegionCode() ), */
            'city' => ( $billingAddress->getCity() != null ) ? ( $billingAddress->getCity() ) : ( $shippingAdress->getCity() ),
            'address' => ( $billingAddress->getStreet() != null ) ? ( join(',', $billingAddress->getStreet()) )  : ( join(',', $shippingAdress->getStreet()) ),
            'zipCode' => ( $billingAddress->getPostcode() != null ) ? ( preg_replace("/[^0-9]/", '', $billingAddress->getPostcode()) ) : ( preg_replace("/[^0-9]/", '', $shippingAdress->getPostcode()) ),
            'phone' => ( $billingAddress->getTelephone() != null ) ? ( preg_replace("/[^0-9\+]/", '', $billingAddress->getTelephone()) ) : ( preg_replace("/[^0-9\+]/", '', $shippingAdress->getTelephone()) ),
            'email' => ( $billingAddress->getEmail() != null ) ? ( $billingAddress->getEmail() ) : ( $shippingAdress->getEmail() )
        ];

        /** Extract the items details */
        $items = array();
        foreach( $order->getAllVisibleItems() as $item){
            $items[] = [
                'item' => $item->getName(),
                'units' => $item->getQtyOrdered(),
                'unitPrice' => number_format( (float)$item->getPriceInclTax(), 2)
            ];
        }

        /* Calculate the backUrl through which the server will pvide the status of the order.
         * TODO
         */
        //$backUrl = get_permalink( get_page_by_path( 'twispay-confirmation' ) );
        //$backUrl .= (FALSE == strpos($backUrl, '?')) ? ('?secure_key=' . $data['cart_hash']) : ('&secure_key=' . $data['cart_hash']);

        $orderData = [
            'siteId' => $siteId,
            'customer' => $customer,
            'order' => [
                'orderId' => $orderId,
                'type' => 'purchase',
                'amount' => $order->getGrandTotal(),
                'currency' => $order->getOrderCurrencyCode(),
                'items' => $items
            ],
            'cardTransactionMode' => 'authAndCapture',
            'invoiceEmail' => '',
            'backUrl' => $this->getBackUrl() /** TO CHECK how is build */
        ];


        $this->log->info('prepareGatewayRequest orderData: ', [print_r($orderData, TRUE)]);
        /** Build the HTML form to be posted in Twispay */
        $base64JsonRequest = $this->getBase64JsonRequest($orderData);
        $base64Checksum = $this->getBase64Checksum($orderData, $secretKey);
        //$hostName = ($this->config && (1 == $this->config->getLiveMode())) ? ('https://secure.twispay.com' . '?lang=' . $lang) : ('https://secure-stage.twispay.com' . '?lang=' . $lang);

        /** NEW IMPLEMENTATION ENDS */

    }

    /**
     * This method computes the checksum on the given data array
     *
     * @param string $encrypted
     * @return array the decrypted response
     * @throws LocalizedException
     */
    public function decryptResponse($encrypted)
    {
        // Get the API key from the cache to be used as a decryption key
        $apiKey = $this->config->getApiKey();

        $encrypted = (string)$encrypted;
        if ($encrypted == "") {
            return null;
        }

        if (strpos($encrypted, ',') !== false) {
            $encryptedParts = explode(',', $encrypted, 2);

            // @codingStandardsIgnoreStart
            $iv = base64_decode($encryptedParts[0]);
            if (false === $iv) {
                throw new LocalizedException(__("Invalid encryption iv"));
            }
            $encrypted = base64_decode($encryptedParts[1]);
            if (false === $encrypted) {
                throw new LocalizedException(__("Invalid encrypted data"));
            }
            // @codingStandardsIgnoreEnd

            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $apiKey, OPENSSL_RAW_DATA, $iv);
            if (false === $decrypted) {
                throw new LocalizedException(__("Data could not be decrypted"));
            }

            return $decrypted;
        }

        return null;
    }

    /**
     * This method receives as a parameter the response from the Twispay gateway
     * and creates the transaction record
     *
     * @param $response
     * @throws PaymentException
     */
    public function processGatewayResponse($response)
    {
        $orderId = (int)$response->externalOrderId;
        $transactionId = (int)$response->transactionId;
        $timestamp = $response->timestamp;

        $details = $response->custom;
        $details['card_id'] = $response->cardId;
        $details['customer'] = $response->identifier;

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);

        if (empty($order) || !$order->getId()) {
            $this->log->error('Order don\'t exists in store', [$orderId]);
            throw new PaymentException(__('Order doesn\'t exists in store'));
        }

        // Add payment transaction
        $payment = $order->getPayment();
        $paymentMethod = $payment->getMethodInstance();

        if ($paymentMethod->getCode() !== \Twispay\Payments\Model\Twispay::METHOD_CODE) {
            $this->log->error('Unsupported payment method', [$paymentMethod->getCode()]);
            throw new PaymentException(__('Unsupported payment method'));
        }

        if ($order->getState() == Order::STATE_PENDING_PAYMENT) {
            $payment->setTransactionId($transactionId);
            $payment->setLastTransId($transactionId);

            // Create the transaction
            /** @var \Magento\Sales\Model\Order\Payment\Transaction $transaction */
            $transaction = $order->getPayment()->addTransaction(Transaction::TYPE_PAYMENT, null, true);
            $transaction->setAdditionalInformation(Transaction::RAW_DETAILS, $details);
            $transaction->setCreatedAt($timestamp);
            $transaction->save();

            $payment->addTransactionCommentsToOrder(
                $transaction,
                __('The authorized amount is %1.', $order->getBaseCurrency()->formatTxt($order->getGrandTotal()))
            );
            $payment->setParentTransactionId(null);
            $payment->save();

            // Update the order state
            $order->setState(Order::STATE_PROCESSING, true);
            $order->setStatus(Order::STATE_PROCESSING);
            $order->setExtCustomerId($response->customerId);
            $order->setExtOrderId($response->orderId);
            $order->addStatusToHistory(
                $order->getStatus(),
                __('Order paid successfully with reference #%1', $transactionId)
            );

            $order->save();
        }
    }
}
