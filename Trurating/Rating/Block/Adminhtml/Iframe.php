<?php
namespace Trurating\Rating\Block\Adminhtml;

class Iframe extends \Magento\Backend\Block\Template
{
	protected $_helper;
	
	protected $logger;
	/**
     * Core store config
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    protected $_storeManager;
      
    /**
     * @param StoreRepository      $storeRepository
     */
    /* 
    * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    */
	public function __construct(
        \Trurating\Rating\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Block\Template\Context $context
    ) {
        $this->_helper = $helper;
        $this->logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

	public function getIframeUrl()
	{
		try 
		{
			$iFrameURL = "https://account.trurating.com/en-us/registration";
			$handle = curl_init($iFrameURL);
			curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

			/* Get the HTML or whatever is linked in $url. */
			$response = curl_exec($handle);

			/* Check for 404 (file not found). */
			$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
			curl_close($handle);

			if($httpCode != 200) {
			    throw new \Exception('Registration link is not accessible. Please contact to support team.');
			}
			
			$urlParams = $this->buildUrlParams();
			$storeManagerDataList = $this->_storeManager->getStores();
			$storeDomain = $this->_helper->getStoreDomains($storeManagerDataList);

			$iFrameURL .= '?'.http_build_query($urlParams).$storeDomain;

			return $iFrameURL;
	
		}
		catch (\Exception $e) {
			$this->logger->critical($e->getMessage());
			$this->_helper->sendRemoteSyslog($e->getMessage(), 'TruRating', $this->_helper->getFqDomain());
		}
	
	}

	public function buildUrlParams()
	{
		$urlParams = array();
		try 
		{
			$urlParams['domainName'] = $this->_helper->stringLimit($this->_helper->getFqDomain(), 100);
			$urlParams['businessName'] = $this->_helper->stringLimit($this->_scopeConfig->getValue('general/store_information/name'), 100);
			$urlParams['addressLine1'] = $this->_helper->stringLimit($this->_scopeConfig->getValue('shipping/origin/street_line1'), 100);
			$urlParams['addressLine2'] = $this->_helper->stringLimit($this->_scopeConfig->getValue('shipping/origin/street_line2'), 100);
			$urlParams['addressCity'] = $this->_helper->stringLimit($this->_scopeConfig->getValue('shipping/origin/city'), 100);
			$urlParams['addressPostCode'] = $this->_helper->stringLimit($this->_scopeConfig->getValue('shipping/origin/postcode'), 100);
			$urlParams['addressCountry'] = $this->_helper->stringLimit($this->_scopeConfig->getValue('shipping/origin/country_id'), 2);
			$urlParams['businessTelephoneNumber'] = $this->_helper->stringLimit($this->_scopeConfig->getValue('general/store_information/phone'), 100);
			$urlParams['extensionVersion'] = $this->_helper->getExtensionVersion('Trurating_Rating');
			$urlParams['shoppingCartPlatform'] = 'magento v'.$this->_helper->getMagentoVersion();
			
			array_walk($urlParams, function (&$entry) { $entry = urlencode($entry); });

		}
		catch (\Exception $e) {
			$this->logger->critical($e->getMessage());
			$this->_helper->sendRemoteSyslog($e->getMessage(), 'TruRating', $this->_helper->getFqDomain());
		}

		return $urlParams;

	}


}
