<?php

/**
 * Copyright (C) 2020 Licentia, Unipessoal LDA
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @title      Licentia Panda - Magento® Sales Automation Extension
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) Licentia - https://licentia.pt
 * @license    GNU General Public License V3
 * @modified   29/01/20, 15:22 GMT
 *
 */

namespace Licentia\Reports\Controller\Adminhtml\PricesVariationsGroups;

/**
 * Class Edit
 *
 * @package Licentia\Reports\Controller\Adminhtml\PricesVariationsGroups
 */
class Edit extends \Licentia\Reports\Controller\Adminhtml\PricesVariationsGroups
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
        $resultPage->setActiveMenu('Licentia_Panda::PricesVariationsGroups')
                   ->addBreadcrumb(__('Sales Automation'), __('Sales Automation'))
                   ->addBreadcrumb(__('Manage Prices Variations Groups'), __('Manage Prices Variations Groups'));

        return $resultPage;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {

        parent::execute();
        $id = $this->getRequest()->getParam('id');

        /** @var \Licentia\Reports\Model\PricesVariationsGroups $model */
        $model = $this->registry->registry('panda_variation_group');

        if ($id) {
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This group mapping no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $model->setGroups(explode(',', $model->getGroups()));

        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Group Mapping') : __('New Group Mapping'),
            $id ? __('Edit Group Mapping') : __('New Group Mapping')
        );
        $resultPage->getConfig()
                   ->getTitle()->prepend(__('Prices Variations Groups'));
        $resultPage->getConfig()
                   ->getTitle()->prepend($model->getId() ? $model->getName() : __('New Group Mapping'));

        $resultPage->addContent(
            $resultPage->getLayout()
                       ->createBlock('Licentia\Reports\Block\Adminhtml\PricesVariationsGroups\Edit')
        )
                   ->addLeft(
                       $resultPage->getLayout()
                                  ->createBlock('Licentia\Reports\Block\Adminhtml\PricesVariationsGroups\Edit\Tabs')
                   );

        return $resultPage;
    }
}
