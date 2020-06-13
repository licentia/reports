<?php
/**
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

namespace Licentia\Reports\Controller\Adminhtml\Search;

/**
 * Class Search
 *
 * @package Licentia\Panda\Controller\Adminhtml\Stats
 */
class Search extends \Licentia\Reports\Controller\Adminhtml\Search
{

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {

        parent::execute();

        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Licentia_Reports::search');

        $resultPage->getConfig()
                   ->getTitle()->prepend(__('Search terms'));

        $block = $resultPage->getLayout()->createBlock('Magento\Backend\Block\Template');

        $block->setTemplate('Licentia_Reports::search.phtml');

        $connection = $this->searchFactory->create()
                                          ->getSearchArray(
                                              $this->getRequest()->getParam('term'),
                                              $this->getRequest()->getParam('sort', 'today'),
                                              $this->getRequest()->getParam('order', 'DESC')
                                          );

        $block->setElastic($connection);

        $resultPage->addContent($block);

        return $resultPage;
    }
}
