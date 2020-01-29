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
