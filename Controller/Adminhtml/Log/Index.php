<?php

namespace Aitoc\Smtp\Controller\Adminhtml\Log;

class Index extends \Aitoc\Smtp\Controller\Adminhtml\Log
{
    /**
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->initPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Emails Log'));

        return $resultPage;
    }
}
