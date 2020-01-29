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

namespace Licentia\Reports\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Class PricesVariationsGroups
 *
 * @package Licentia\Panda\Controller\Adminhtml
 */
class PricesVariationsGroups extends \Magento\Backend\App\Action
{

    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Licentia_Panda::prices_variation';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Licentia\Reports\Model\PricesVariationsGroupsFactory
     */
    protected $pricesVariationsGroupsFactory;

    /**
     * PricesVariationsGroups constructor.
     *
     * @param Action\Context                                        $context
     * @param \Magento\Framework\View\Result\PageFactory            $resultPageFactory
     * @param \Magento\Framework\Registry                           $registry
     * @param \Licentia\Reports\Model\PricesVariationsGroupsFactory $pricesVariationsGroupsFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory     $resultForwardFactory
     * @param \Magento\Framework\View\Result\LayoutFactory          $resultLayoutFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Licentia\Reports\Model\PricesVariationsGroupsFactory $pricesVariationsGroupsFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {

        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->layoutFactory = $resultLayoutFactory;
        $this->pricesVariationsGroupsFactory = $pricesVariationsGroupsFactory;
    }

    /**
     *
     */
    public function execute()
    {

        /** @var \Licentia\Reports\Model\PricesVariationsGroups $model */
        $model = $this->pricesVariationsGroupsFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $model->load($id);
        }

        $this->registry->register('panda_variation_group', $model, true);
    }

}
