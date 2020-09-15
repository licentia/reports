<?php
/*
 * Copyright (C) Licentia, Unipessoal LDA
 *
 * NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  https://www.greenflyingpanda.com/panda-license.txt
 *
 *  @title      Licentia Panda - Magento® Sales Automation Extension
 *  @package    Licentia
 *  @author     Bento Vilas Boas <bento@licentia.pt>
 *  @copyright  Copyright (c) Licentia - https://licentia.pt
 *  @license    https://www.greenflyingpanda.com/panda-license.txt
 *
 */

namespace Licentia\Reports\Controller\Adminhtml\Searches;

/**
 * Class Index
 *
 * @package Licentia\Panda\Controller\Adminhtml\Stats
 */
class Index extends \Licentia\Reports\Controller\Adminhtml\Stats
{

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {

        parent::execute();

        $title = __('Product Searches');

        if (!$this->pandaHelper->tableHasRecords('panda_search_relations_global')) {
            $this->messageManager->addWarning(
                __(
                    'No Reports Build. Please check the manual on how to build reports. ' .
                    'If you want to reindex everything again, or if you just installed the extension, ' .
                    '<a href="%1">click here </a>. We recommend in alternative to run this command from the ' .
                    'command line: <pre>php bin/magento panda:rebuild</pre>',
                    $this->getUrl('*/relations/rebuildAll')
                )
            );
            $this->messageManager->addWarning(
                __(
                    "Please note search reports are based on logged-in customers searches. If no searches have been made, this message will always be displayed"
                )
            );
            return $this->_redirect('pandar/indexer');
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Licentia_Reports::relations')
                   ->addBreadcrumb(__('Sales Automation'), __('Sales Automation'))
                   ->addBreadcrumb(__('Search Stats'), __('Search Stats'));

        $resultPage->getConfig()->getTitle()->prepend($title);

        if ($this->getRequest()->getParam('cloud') == 1) {
            $resultPage->addContent(
                $resultPage->getLayout()
                           ->createBlock('Licentia\Reports\Block\Adminhtml\Searches\InfoCloud')
            );

            $resultPage->addContent(

                $resultPage->getLayout()
                           ->createBlock('Licentia\Reports\Block\Adminhtml\Searches\Cloud')
            );
        } else {
            $resultPage->addContent(
                $resultPage->getLayout()
                           ->createBlock('Licentia\Reports\Block\Adminhtml\Searches\View')
            );
        }

        return $resultPage;
    }
}
