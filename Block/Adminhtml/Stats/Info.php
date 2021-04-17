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

namespace Licentia\Reports\Block\Adminhtml\Stats;

/**
 * Class Info
 *
 * @package Licentia\Panda\Block\Adminhtml\Stats
 */
class Info extends \Magento\Backend\Block\Template
{

    /**
     * @var \Licentia\Reports\Model\Sales\StatsFactory
     */
    protected \Licentia\Reports\Model\Sales\StatsFactory $statsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected \Magento\Catalog\Model\ProductFactory $productFactory;

    /**
     * @var
     */
    protected $collection;

    /**
     * @var
     */
    protected $collectionPeriods;

    /**
     * @var \Licentia\Equity\Model\SegmentsFactory
     */
    protected \Licentia\Equity\Model\SegmentsFactory $segmentsFactory;

    /**
     * @var \Licentia\Reports\Model\Sales\Orders
     */
    protected \Licentia\Reports\Model\Sales\Orders $salesorder;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected \Magento\Framework\Pricing\Helper\Data $priceHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected \Magento\Framework\Registry $registry;

    /**
     * Info constructor.
     *
     * @param \Magento\Framework\Registry                $registry
     * @param \Licentia\Reports\Model\Sales\Orders       $salesorder
     * @param \Licentia\Equity\Model\SegmentsFactory     $segmentsFactory
     * @param \Magento\Framework\Pricing\Helper\Data     $priceHelper
     * @param \Licentia\Reports\Model\Sales\StatsFactory $statsFactory
     * @param \Magento\Catalog\Model\ProductFactory      $productFactory
     * @param \Magento\Backend\Block\Template\Context    $context
     * @param array                                      $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Licentia\Reports\Model\Sales\Orders $salesorder,
        \Licentia\Equity\Model\SegmentsFactory $segmentsFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Licentia\Reports\Model\Sales\StatsFactory $statsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {

        parent::__construct($context, $data);

        $this->priceHelper = $priceHelper;
        $this->statsFactory = $statsFactory;
        $this->productFactory = $productFactory;
        $this->segmentsFactory = $segmentsFactory;
        $this->salesorder = $salesorder;
        $this->registry = $registry;

        $this->setTemplate('Licentia_Reports::stats/info.phtml');
    }

    /**
     * @return array
     */
    public function getGroups()
    {

        $blocks = \Licentia\Reports\Model\Sales\Orders::getGroups();
        $blocks['single'] = __('Single Record');

        return $blocks;
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {

        return $this->registry;
    }

    /**
     * @return string
     */
    public function getGroup()
    {

        $type = strtolower(
            $this->getRequest()->getParam('group_results', 'date')
        );

        if (!in_array($type, array_keys($this->getGroups()))) {
            $type = 'month';
        }

        return $type;
    }

    /**
     * @return array
     */
    public function getTypes()
    {

        return $this->salesorder->getTypes();
    }

    /**
     * @return string
     */
    public function getType()
    {

        $type = strtolower(
            $this->getRequest()->getParam('type', 'global')
        );

        if (!in_array($type, array_keys($this->getTypes()))) {
            $type = 'global';
        }

        return $type;
    }

    /**
     * @return mixed
     */
    public function getSegmentId()
    {

        return $this->getRequest()->getParam('segment_id');
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getSegmentsList()
    {

        $collection = $this->segmentsFactory->create()
                                            ->getCollection()
                                            ->addFieldToSelect(['segment_id', 'name'])
                                            ->addFieldToFilter('products_relations', 1)
                                            ->setOrder('name', 'ASC');

        return $collection;
    }
}
