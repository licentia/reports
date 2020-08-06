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

namespace Licentia\Reports\Controller\Adminhtml\Indexer;

/**
 * Class Reindex
 *
 * @package Licentia\Panda\Controller\Adminhtml\Indexer
 */
class Reindex extends \Licentia\Reports\Controller\Adminhtml\Indexer
{

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        parent::execute();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->getParam('op') == 'all') {
            $this->pandaHelper->scheduleEvent('panda_indexer_rebuild_invalidated');

            $this->messageManager->addSuccessMessage(__('Indexes scheduled to rebuild'));
        } else {
            $model = $this->indexerFactory->create()->load($this->getRequest()->getParam('id'));

            if ($model->getId()) {
                try {
                    $model->reindex();
                    $this->messageManager->addSuccessMessage(__('Index scheduled to rebuild'));

                    return $resultRedirect->setPath('*/*/');
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('Something went wrong while rebuilding the Index.')
                    );
                }

                return $resultRedirect->setPath('*/*/');
            } else {
                $this->messageManager->addErrorMessage(__('We can\'t find a index to rebuilding.'));
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
