<?php
namespace Trurating\Rating\Setup;
use Magento\Framework\Notification\NotifierInterface as NotifierPool;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface {
	protected $_helperemail;
	protected $notifierPool;

	public function __construct(
		NotifierPool $notifierPool,
		\Trurating\Rating\Helper\Email $helperemail
	) {
		$this->notifierPool = $notifierPool;
		$this->_helperemail = $helperemail;
	}
	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
		$this->notifierPool->addCritical('Register now for TruRating Online', "You're almost there! Let's get you registered for TruRating Online so you can start improving your business with powerful customer insights.", 'http://demo-ecomm.trurating.com/');
		$this->_helperemail->sendNotification();
		

	}
}
