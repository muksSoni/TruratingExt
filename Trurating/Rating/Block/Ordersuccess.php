<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Trurating\Rating\Block;
class Ordersuccess  extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_helper;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;
    protected $date;
    protected $_scopeConfig;
    protected $_order;
    protected $moduleReader;
    /**
 * @var CategoryListInterface
 */
private $categoryListRepository;

/**
 * @var SearchCriteriaBuilder
 */
private $searchCriteriaBuilder;
protected $sessionManager;

    protected $_currencyArr = array();

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Trurating\Rating\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Catalog\Api\CategoryListInterface $categoryListRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Session\SessionManagerInterface $SessionManagerInterface,
        array $data = []
    ) {
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
        $this->_helper = $helper;
        $this->date = $date;
        $this->_scopeConfig = $scopeConfig;
        $this->_order = $order;
        $this->moduleReader = $moduleReader;
        $this->categoryListRepository = $categoryListRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sessionManager = $SessionManagerInterface;
    }

    
    public function getCurrencyXmlData(){

        $configFile = $this->moduleReader->getModuleDir('etc', 'Trurating_Rating').'/currencies.xml';
        $xml = simplexml_load_string(file_get_contents($configFile));
        $json  = json_encode($xml);
        $configData = json_decode($json, true);

        try 
        {
            if(isset($configData['currency']) && !empty($configData['currency'])) {
                foreach($configData['currency'] as $CurrencyXmlData):
                    $this->_currencyArr[$CurrencyXmlData['alphaCode']]['numericCode'] = $CurrencyXmlData['numericCode'];
                    $this->_currencyArr[$CurrencyXmlData['alphaCode']]['minorUnitDigits'] = $CurrencyXmlData['minorUnitDigits'];
                endforeach;
            } else {
                throw new \Exception('Currency XML empty or missing.');
            }
        }
        catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->_helper->sendRemoteSyslog($e->getMessage(), 'TruRating', $this->_helper->getFqDomain());
        }

    }

    public function getSomething($orderId)
    {
        $dataArray = array();

        $this->getCurrencyXmlData();
        //echo $this->_helper->testme();
        $orderObj = $this->_order->load($orderId);
        
        $dataArray['transactionId'] = $orderId;
        $dataArray['transactionDateTime'] = $this->date->gmtDate('Y-m-d\Th:i:s\Z');
        $dataArray['isTransactionSuccess'] = true;
        $dataArray['isLive'] = true;
        $dataArray['languageCode'] = strtok($this->_scopeConfig->getValue('general/locale/code'), '_');
        $dataArray['countryCode'] = $orderObj->getBillingAddress()->getCountryId();
        $dataArray['customerId'] = md5($orderObj->getCustomerEmail());
        $dataArray['currency'] = $this->getCurrencyNumber($orderObj->getOrderCurrencyCode());
        $dataArray['transactionAmount'] = $this->getMinorCurrency($orderObj->getGrandTotal(),$orderObj->getOrderCurrencyCode());
		$dataArray['shoppingBasket'] = $this->getOrderItem($orderObj);

		$dataArray['utmParameters'] = $this->getutmParameters();

		$dataArray['shoppingCartPlatform'] = 'Magento ' . $this->_helper->getMagentoVersion();
		$dataArray['extensionVersion'] = $this->_helper->getExtensionVersion('Trurating_Rating');

        return $dataArray;
    }


    public function getCurrencyNumber($code = null) {
        $currencyNo = null;
        if(array_key_exists($code, $this->_currencyArr)) {
            $currencyNo = $this->_currencyArr[$code]['numericCode'];
        }

        return $currencyNo;
    }

    public function getMinorCurrency($amount, $currencyCode){
        $minorUnitDigits = null;
        if(array_key_exists($currencyCode, $this->_currencyArr)) {
            $minorUnitDigits = $this->_currencyArr[$currencyCode]['minorUnitDigits'];
        }
        
        $finalAmount = number_format((float)$amount, $minorUnitDigits, '.', '');
        
        return $minorCurrencyPrice = $finalAmount * (pow(10, $minorUnitDigits));
    }


    public function getOrderItem($orderObj){
        $orderedItems = array();
        $counter = 0;
        $subTotalAmount = '';
        
        //$orderObj->getAllVisibleItems()
        foreach($orderObj->getAllItems() as $item) {
            $orderedItemData[$counter]['skuCode'] = $item->getSku();
            $orderedItemData[$counter]['unitAmount'] = (int)$item->getQtyOrdered();
            $orderedItemData[$counter]['description'] = $this->_helper->stringLimit($item->getProduct()->getShortDescription(),100);
            $orderedItemData[$counter]['retailAmount'] = $this->getMinorCurrency($item->getProduct()->getPrice(),$orderObj->getOrderCurrencyCode());
            $orderedItemData[$counter]['sellingAmount'] = $this->getMinorCurrency($item->getPrice(),$orderObj->getOrderCurrencyCode());
            $orderedItemData[$counter]['category'] = $this->getCategoryName($item->getProduct()->getCategoryIds());

            /*$orderedItemData[$counter]['category11'] = $item->getProduct()->getCategoryIds();*/
            $orderedItemData[$counter]['department'] = '';
            $subTotalAmount += $item->getPrice() * (int)$item->getQtyOrdered();
            $counter++;
        }

        $orderedItems['items'] = $orderedItemData;
        $orderedItems['discountCodes'] = ($orderObj->getCouponCode() != '') ? array($orderObj->getCouponCode()) : array();
        $orderedItems['deliveryCharge'] = $this->getMinorCurrency($orderObj->getShippingAmount(),$orderObj->getOrderCurrencyCode());
        $orderedItems['deliveryMechanism'] = $this->_helper->stringLimit($orderObj->getShippingDescription(),100);
        $orderedItems['subTotalAmount'] = $this->getMinorCurrency($subTotalAmount,$orderObj->getOrderCurrencyCode());
        
        return $orderedItems;
    }

    public function getCategoryName($categoryIds){
        $categoryData = array();

        $this->searchCriteriaBuilder->addFilter('entity_id', $categoryIds, 'in');
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $results = $this->categoryListRepository->getList($searchCriteria);

        foreach ($results->getItems() as $value) {
           $categoryData[] = $value->getName();
        }

        return $categoryData;
    }

    public function getutmParameters(){
        $utmParameters = array();
        
        $utmData = unserialize($this->sessionManager->getUtmData());
        //$utmData = array();
        
        $utmParameters['utmSource'] = (isset($utmData['utm_source'])) ? $utmData['utm_source'] : '';
        $utmParameters['utmMedium'] = (isset($utmData['utm_medium'])) ? $utmData['utm_medium'] : '';
        $utmParameters['utmCampaign'] = (isset($utmData['utm_campaign'])) ? $utmData['utm_campaign'] : '';
        $utmParameters['utmTerm'] = (isset($utmData['utm_term'])) ? $utmData['utm_term'] : '';
        $utmParameters['utmContent'] = (isset($utmData['utm_content'])) ? $utmData['utm_content'] : '';
        
        $this->sessionManager->unsUtmData();
        return $utmParameters;
    }
    
}