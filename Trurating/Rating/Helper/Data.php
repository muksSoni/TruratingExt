<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Trurating\Rating\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\Store;
use Magento\Store\Model\ScopeInterface;

/**
 * Checkout default helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_GUEST_CHECKOUT = 'checkout/options/guest_checkout';

    /* Senbox Account */
    const LOG_DESTINATION = 'logs2.papertrailapp.com';
    const PAPERTRAILAPP_PORT = '50403';

    /* Live Account */
    /*const LOG_DESTINATION = 'logs3.papertrailapp.com';
    const PAPERTRAILAPP_PORT = '47662';*/

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;
    protected $_request;
    protected $filterManager;

    /**
     * @var PriceCurrencyInterface
     */
    
    protected $logger;
    protected $productMetadata;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    protected $priceCurrency;
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_localeDate = $localeDate;
        $this->_request = $request;
        $this->filterManager = $filterManager;
        $this->logger = $logger;
        $this->productMetadata = $productMetadata;
        $this->_moduleList = $moduleList;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context);
    }

    /**
     * @param $content
     * @return mixed
     */
    public function stringLimit($content,$length){
        //$stringData = Mage::helper('core/string')->truncate($content, $length);
        $stringData = $this->filterManager->truncate($content, ['length' => $length, 'etc' => '']);
        return $stringData;     
    }

    /**
     * @param float $price
     * @return string
     */
    public function getFqDomain(){
        //return Mage::helper('core/http')->getHttpHost();
        return $this->_request->getHttpHost(); 
    }

    public function sendRemoteSyslog($message, $component = "TruRating", $program = "Magento") {
        try 
        {
            $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            foreach(explode("\n", $message) as $line) 
            {
                $syslog_message = "<22>" . date('M d H:i:s ') . $program . ' ' . $component . ': ' . $line;
                socket_sendto($sock, $syslog_message, strlen($syslog_message), 0, self::LOG_DESTINATION, self::PAPERTRAILAPP_PORT);
            }
            socket_close($sock);
        }
        catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }

    public function getExtensionVersion($moduleCode = '')
    {
        $extensionVersion = '';
        if($moduleCode){
            $moduleInfo = $this->_moduleList->getOne($moduleCode);
            $extensionVersion = $moduleInfo['setup_version'];
        }
        
        return $extensionVersion;
    }

    public function getStoreDomains($storeManagerDataList=null)
    {
        $stores = '';
        if($storeManagerDataList) {
            foreach ($storeManagerDataList as $key => $value) {
                $stores .= '&secondary-store-domain-name='.urlencode($value['name']);
            }
        }
        
        return $stores;
    }

    public function testme()
    {
        echo $this->_storeManager->getStore()->getBaseUrl();
    }
}