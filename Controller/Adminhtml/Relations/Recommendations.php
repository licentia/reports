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

namespace Licentia\Reports\Controller\Adminhtml\Relations;

/**
 * Class Recommendations
 *
 * @package Licentia\Panda\Controller\Adminhtml\Stats
 */
class Recommendations extends \Licentia\Reports\Controller\Adminhtml\Stats
{

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {

        parent::execute();

        if (!$this->pandaHelper->tableHasRecords('panda_products_recommendations_global')) {
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
                    "Please note reports are based on Orders data. If you don't have any orders, this message will always be displayed"
                )
            );
            return $this->_redirect('pandar/indexer');
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Licentia_Reports::relations')
                   ->addBreadcrumb(__('Sales Automation'), __('Sales Automation'))
                   ->addBreadcrumb(__('Product Recommendation'), __('Product Recommendation'));

        $resultPage->getConfig()
                   ->getTitle()->prepend(__('Product Recommendations'));

        $resultPage->addContent(
            $resultPage->getLayout()
                       ->createBlock('Licentia\Reports\Block\Adminhtml\Relations\Recommendations')
        );

        return $resultPage;
    }
}
