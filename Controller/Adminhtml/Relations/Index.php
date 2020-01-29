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
 * Class Index
 *
 * @package Licentia\Panda\Controller\Adminhtml\Stats
 */
class Index extends \Licentia\Reports\Controller\Adminhtml\Stats
{

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {

        parent::execute();

        $attributes = ($this->getRequest()->getParam('attributes') == 1);

        $title = __('Product Relations');
        if ($attributes) {
            $title = __('Attributes Relations');
        }

        if (!$this->pandaHelper->tableHasRecords('panda_products_relations_global')) {
            $this->messageManager->addWarning(
                __(
                    'No Reports Build. Please check the manual on how to build reports. ' .
                    'If you just installed the extension you must run this command from the command line: ' .
                    '<pre>php bin/magento panda:rebuild</pre>'
                )
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Licentia_Reports::relations')
                   ->addBreadcrumb(__('Sales Automation'), __('Sales Automation'))
                   ->addBreadcrumb(__('Sales Stats'), __('Sales Stats'));

        $resultPage->getConfig()->getTitle()->prepend($title);

        $resultPage->addContent(
            $resultPage->getLayout()
                       ->createBlock('Licentia\Reports\Block\Adminhtml\Relations\View')
        );

        return $resultPage;
    }
}
