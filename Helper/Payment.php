<?php

namespace Twispay\Payments\Helper;

/**
 * Helper class for everything that has to do with payment
 */
class Payment extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
	 * Store manager object
	 *
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $storeManager;

	/**
	 * @var \Twispay\Payments\Logger\Logger
	 */
	protected $log;

	/**
	 * Constructor
	 *
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Twispay\Payments\Model\Config $config
	 * @param \Twispay\Payments\Logger\Logger $twispayLogger
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Twispay\Payments\Model\Config $config,
		\Twispay\Payments\Logger\Logger $twispayLogger,
		\Magento\Store\Model\StoreManagerInterface $storeManager
	) {
		parent::__construct($context);
		$this->config = $config;
		$this->log = $twispayLogger;
		$this->storeManager = $storeManager;
	}

	/**
	 * This method computes the checksum on the given data array
	 *
	 * @param array $data
	 * @return string the computed checksum
	 */
	public function computeChecksum(array &$data) {
		$apiKey = $this->config->getApiKey();

		$this->log->debug($apiKey);

		$this->recursiveKeySort($data);

		$query = http_build_query($data);

		$this->log->debug($query);

		$encoded = hash_hmac('sha512', $query, $apiKey, true);

		return base64_encode($encoded);
	}

	/**
	 * Sort the array based on the keys
	 * @param array $data
	 */
	private function recursiveKeySort(array &$data) {
		ksort($data, SORT_STRING);
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$this->recursiveKeySort($data[$key]);
			}
		}
	}
}