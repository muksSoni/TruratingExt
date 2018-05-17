<?php

use Magento\Framework\Session\SessionManagerInterface;
namespace Trurating\Rating\Observer;

class Preaction implements \Magento\Framework\Event\ObserverInterface
{
  
  protected $_helper;
  /**
   * @var \Magento\Framework\Session\SessionManagerInterface
  **/
  protected $sessionManager;
	
   
  public function __construct(
	  \Trurating\Rating\Helper\Data $helper,
	  \Magento\Framework\Session\SessionManagerInterface $SessionManagerInterface
  ) {
       $this->_helper = $helper;
	  $this->sessionManager = $SessionManagerInterface;
  }
  
  
  public function execute(\Magento\Framework\Event\Observer $observer)
  {
	 
	 try {
		$params = $observer->getRequest()->getParams(); 
		$utmData = unserialize($this->sessionManager->getUtmData());
		
		$params['utm_source'] = (isset($params['utm_source']) && $params['utm_source']) ? $params['utm_source']: $utmData['utm_source'];
	    $params['utm_medium'] = (isset($params['utm_medium']) && $params['utm_medium']) ? $params['utm_medium']: $utmData['utm_medium'];
	    $params['utm_content'] = (isset($params['utm_content']) && $params['utm_content']) ? $params['utm_content']: $utmData['utm_content'];
	    $params['utm_campaign'] = (isset($params['utm_campaign']) && $params['utm_campaign']) ? $params['utm_campaign']: $utmData['utm_campaign'];
	    $params['utm_term'] = (isset($params['utm_term']) && $params['utm_term'])?$params['utm_term']: $utmData['utm_term'];
		
		$this->sessionManager->setUtmData(serialize($params));
        /* $utmData = unserialize($this->sessionManager->getUtmData());
   		echo '<pre>';
		print_r($params);
		echo '</pre>'; */
	 }
	 catch (\Exception $e) {
		$this->_helper->sendRemoteSyslog($e->getMessage(), 'TruRating', $this->_helper->getFqDomain());
	 }
     return $this;
	
  }
  
}