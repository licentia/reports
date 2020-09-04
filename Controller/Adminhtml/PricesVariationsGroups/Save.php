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
 * Class Save
 *
 * @package Licentia\Reports\Controller\Adminhtml\PricesVariationsGroups
 */
class Save extends \Licentia\Reports\Controller\Adminhtml\PricesVariationsGroups
{

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        parent::execute();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = $this->getRequest()->getParam('id');

            /** @var \Licentia\Reports\Model\PricesVariationsGroups $model */
            $model = $this->registry->registry('panda_variation_group');

            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Group Mapping no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }
            if (!isset($data['groups'])) {
                $data['groups'] = [];
            }

            $data['groups'] = implode(',', $data['groups']);

            $model->setData($data);
            $model->setId($id);

            try {

                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the Group Mapping.'));

                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        [
                            'id'     => $model->getId(),
                            'active_tab' => $this->getRequest()->getParam('active_tab'),
                        ]
                    );
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the Group Mapping. Check the error log for more information.')
                );
            }

            $this->_getSession()->setFormData($data);

            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'id' => $model->getId(),
                ]
            );
        }

        return $resultRedirect->setPath('*/*/');
    }
}
