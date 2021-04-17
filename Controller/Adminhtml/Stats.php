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
 * Newsletter subscribers controller
 */
class Stats extends Action
{

    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Licentia_Reports::relations';

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
     * @var \Licentia\Reports\Model\Sales\StatsFactory
     */
    protected \Licentia\Reports\Model\Sales\StatsFactory $statsFactory;

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected \Licentia\Reports\Helper\Data $pandaHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected \Magento\Catalog\Model\ProductFactory $productFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected \Magento\Framework\App\Response\Http\FileFactory $fileFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    /**
     * @param Action\Context                                    $context
     * @param \Magento\Framework\App\Response\Http\FileFactory  $fileFactory
     * @param \Magento\Catalog\Model\ProductFactory             $productFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory  $resultJsonFactory
     * @param \Magento\Framework\View\Result\PageFactory        $resultPageFactory
     * @param \Magento\Framework\Registry                       $registry
     * @param \Licentia\Reports\Helper\Data                     $pandaHelper
     * @param \Licentia\Reports\Model\Sales\StatsFactory        $statsFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\LayoutFactory      $resultLayoutFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Licentia\Reports\Helper\Data $pandaHelper,
        \Licentia\Reports\Model\Sales\StatsFactory $statsFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {

        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->layoutFactory = $resultLayoutFactory;
        $this->statsFactory = $statsFactory;
        $this->pandaHelper = $pandaHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productFactory = $productFactory;
        $this->fileFactory = $fileFactory;
        $this->scopeConfig = $scopeConfigInterface;

        parent::__construct($context);
    }

    /**
     *
     */
    public function execute()
    {
    }

}
