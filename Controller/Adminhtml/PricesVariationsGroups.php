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

namespace Licentia\Reports\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Class PricesVariationsGroups
 *
 * @package Licentia\Panda\Controller\Adminhtml
 */
class PricesVariationsGroups extends Action
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
    protected ?\Magento\Framework\Registry $registry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected \Magento\Framework\View\Result\PageFactory $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected \Magento\Framework\View\Result\LayoutFactory $layoutFactory;

    /**
     * @var \Licentia\Reports\Model\PricesVariationsGroupsFactory
     */
    protected \Licentia\Reports\Model\PricesVariationsGroupsFactory $pricesVariationsGroupsFactory;

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
