<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Trurating\Rating\Helper;

/**
 * Checkout default helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $productMetadata;
    protected $_scopeConfig;
    /** @var \Magento\Framework\App\State **/
protected $state;


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_transportBuilder = $transportBuilder;
        $this->productMetadata = $productMetadata;
        $this->_scopeConfig = $scopeConfig;
        $this->state = $state;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }
 
    public function sendNotification()
    {
        $MVer = $this->productMetadata->getVersion();
        $contactNumber = $this->_scopeConfig->getValue('general/store_information/phone');
        $businessAddressLineA = $this->_scopeConfig->getValue('shipping/origin/street_line1');

        /* Receiver Detail  */
        $receiverInfo = [
            'name' => 'Suman Singh',
            'email' => 'suman.singh@dotsquares.com'
        ];
         
        // Sender Detail
        $senderInfo = [
            'name' => 'Trurating',
            'email' => 'mukesh.soni@dotsquares.com',
        ];
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

        $transport = $this->_transportBuilder->setTemplateIdentifier('trurating_rating_installation_notify_email_template')
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->_storeManager->getStore()->getId()])
            ->setTemplateVars(
                [
                    'store' => $this->_storeManager->getStore(),
                    'mver' => $MVer,
                    'contactnumber' => $contactNumber,
                    'contactaddress' => $businessAddressLineA
                ]
            )
            ->setFrom($senderInfo)
            // you can config general email address in Store -> Configuration -> General -> Store Email Addresses
            ->addTo($receiverInfo['email'],$receiverInfo['name'])
            ->getTransport();
        $transport->sendMessage();

    }
    public function sendNotification2()
    {
        echo "test email helper";
    }

}