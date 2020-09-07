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
 * Class Performance
 *
 * @package Licentia\Panda\Controller\Adminhtml\Stats
 */
class Performance extends \Licentia\Reports\Controller\Adminhtml\Stats
{

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {

        parent::execute();
        if (!$this->pandaHelper->tableHasRecords('panda_search_performance_global')) {
            $this->messageManager->addWarning(
                __(
                    'No Reports Build. Please check the manual on how to build reports. ' .
                    'If you want to reindex everything again, or if you just installed the extension, ' .
                    '<a href="%1">click here </a>. We recommend in alternative to run this command from the ' .
                    'command line: <pre>php bin/magento panda:rebuild</pre>',
                    $this->getUrl('*/relations/rebuildAll')
                )
            );
            return $this->_redirect('pandar/indexer');
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Licentia_Reports::productsperformance')
                   ->addBreadcrumb(__('Sales Automation'), __('Sales Automation'))
                   ->addBreadcrumb(__('Searches Performance'), __('Searches Performance'));

        $resultPage->getConfig()
                   ->getTitle()->prepend(__('Searches Performance'));

        $html = $resultPage->getLayout()->createBlock('Licentia\Reports\Block\Adminhtml\Searches\Grid')->_prepareGrid();

        $resultPage->addContent(
            $resultPage->getLayout()
                       ->createBlock('Licentia\Reports\Block\Adminhtml\Searches\Info')
        );
        $resultPage->addContent(
            $html
        );

        return $resultPage;
    }
}
