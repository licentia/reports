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

namespace Licentia\Reports\Controller\Adminhtml\Recommendations;

/**
 * Class Edit
 *
 * @package Licentia\Panda\Controller\Adminhtml\Recommendations
 */
class Edit extends \Licentia\Reports\Controller\Adminhtml\Recommendations
{

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {

        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Licentia_Reports::recommendations')
                   ->addBreadcrumb(__('Types'), __('Recommendations'))
                   ->addBreadcrumb(__('Manage Recommendations'), __('Manage Recommendations'));

        return $resultPage;
    }

    /**
     * @return $this|\Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {

        parent::execute();
        $id = $this->getRequest()->getParam('id');
        /** @var \Licentia\Reports\Model\Recommendations $model */
        $model = $this->registry->registry('panda_recommendation');

        if ($id && !$model->getId()) {
            $this->messageManager->addErrorMessage(__('This Recommendation no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $rName = '';
        if ($model->getId()) {
            $rName = \Licentia\Reports\Model\Recommendations::getRecommendationsTypes();
            $rName = $model->getInternalName() . ' (' . $rName[$model->getEntityType()] . ')';
        }

        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Recommendation') : __('New Recommendation'),
            $id ? __('Edit Recommendation') : __('New Recommendation')
        );
        $resultPage->getConfig()
                   ->getTitle()->prepend(__('Recommendations'));
        $resultPage->getConfig()
                   ->getTitle()->prepend(
                $model->getId() ? __('Edit') . ' ' . $rName : __('New Recommendation')
            );

        $resultPage->addContent(
            $resultPage->getLayout()
                       ->createBlock('Licentia\Reports\Block\Adminhtml\Recommendations\Edit')
        )
                   ->addLeft(
                       $resultPage->getLayout()
                                  ->createBlock('Licentia\Reports\Block\Adminhtml\Recommendations\Edit\Tabs')
                   );

        return $resultPage;
    }
}
