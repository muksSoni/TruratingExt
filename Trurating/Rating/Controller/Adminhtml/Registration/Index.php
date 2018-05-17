<?php
namespace Trurating\Rating\Controller\Adminhtml\Registration;
 
class Index extends \Magento\Backend\App\Action
{
     /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        
        //$resultPage = $this->resultPageFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Trurating_Rating::trurating_online');
        $resultPage->addBreadcrumb(__('TruRating Online'), __('TruRating Online'));
        $resultPage->getConfig()->getTitle()->prepend(__('TruRating Online'));
        return $resultPage;
    }
 
    /**
     * Is the user allowed to view the blog post grid.
     *
     * @return bool
     */

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Trurating_Rating::registration');
    }
}