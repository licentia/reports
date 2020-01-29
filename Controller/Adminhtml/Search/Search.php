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
