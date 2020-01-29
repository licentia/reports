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
