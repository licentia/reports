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
class Searches extends Action
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
     * @var \Licentia\Reports\Model\Search\StatsFactory
     */
    protected \Licentia\Reports\Model\Search\StatsFactory $statsFactory;

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected \Licentia\Reports\Helper\Data $pandaHelper;

    /**
     * @param Action\Context                                    $context
     * @param \Magento\Framework\View\Result\PageFactory        $resultPageFactory
     * @param \Magento\Framework\Registry                       $registry
     * @param \Licentia\Reports\Helper\Data                     $pandaHelper
     * @param \Licentia\Reports\Model\Search\StatsFactory       $statsFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\LayoutFactory      $resultLayoutFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Licentia\Reports\Helper\Data $pandaHelper,
        \Licentia\Reports\Model\Search\StatsFactory $statsFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {

        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->layoutFactory = $resultLayoutFactory;
        $this->statsFactory = $statsFactory;
        $this->pandaHelper = $pandaHelper;
        parent::__construct($context);
    }

    /**
     *
     */
    public function execute()
    {
    }

}
