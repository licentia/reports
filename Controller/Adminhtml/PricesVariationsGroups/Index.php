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

namespace Licentia\Reports\Controller\Adminhtml\PricesVariationsGroups;

/**
 * Class Index
 *
 * @package Licentia\Reports\Controller\Adminhtml\PricesVariationsGroups
 */
class Index extends \Licentia\Reports\Controller\Adminhtml\PricesVariationsGroups
{

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Licentia_Panda::PricesVariationsGroups');
        $resultPage->getConfig()
                   ->getTitle()->prepend(__('Prices Variations Groups'));
        $resultPage->addBreadcrumb(__('Sales Automation'), __('Sales Automation'));
        $resultPage->addBreadcrumb(__('Prices Variations Groups'), __('Prices Variations Groups'));

        return $resultPage;
    }
}
