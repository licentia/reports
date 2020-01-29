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

namespace Licentia\Reports\Controller\Adminhtml\Relations;

/**
 * Class RebuildAll
 *
 * @package Licentia\Panda\Controller\Adminhtml\Stats
 */
class RebuildAll extends \Licentia\Reports\Controller\Adminhtml\Stats
{

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {

        parent::execute();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $this->pandaHelper->scheduleEvent('panda_rebuild_everything');

        $this->messageManager->addSuccessMessage(__('Data will start building next time your cron runs. ' .
                                                    'This might take a few hours'));

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
