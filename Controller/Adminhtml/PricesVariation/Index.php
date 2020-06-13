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

namespace Licentia\Reports\Controller\Adminhtml\PricesVariation;

/**
 * Class Index
 *
 * @package Licentia\Reports\Controller\Adminhtml\PricesVariation
 */
class Index extends \Licentia\Reports\Controller\Adminhtml\PricesVariation
{

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {

        parent::execute();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Licentia_Reports::prices_variation');
        $resultPage->getConfig()->getTitle()->prepend(__('Prices Variation'));
        $resultPage->addBreadcrumb(__('Sales Automation'), __('Sales Automation'));
        $resultPage->addBreadcrumb(__('Prices Variation'), __('Prices Variation'));

        if ($this->getRequest()->getParam('rebuild') == 1) {
            $this->pricesvariationFactory->create()->clearPricesVariation();
            $this->pandaHelper->scheduleEvent('panda_build_prices_variation');
            $this->messageManager->addSuccessMessage('Data Cleared. Will be rebuilt next time your CRON runs');

            return $this->_redirect('*/*/*');
        }

        if ($this->getRequest()->getParam('update') == 1) {
            $this->pandaHelper->scheduleEvent('panda_build_prices_variation');
            $this->messageManager->addSuccessMessage('Data will be rebuilt next time your CRON runs');

            return $this->_redirect('*/*/*');
        }

        return $resultPage;
    }
}
