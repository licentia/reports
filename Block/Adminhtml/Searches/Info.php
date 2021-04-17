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

namespace Licentia\Reports\Block\Adminhtml\Searches;

/**
 * Class Info
 *
 * @package Licentia\Panda\Block\Adminhtml\Stats
 */
class Info extends \Magento\Backend\Block\Template
{

    /**
     * @var \Licentia\Reports\Model\Search\StatsFactory
     */
    protected $searchStatsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

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
    protected $segmentsFactory;

    /**
     * Info constructor.
     *
     * @param \Licentia\Equity\Model\SegmentsFactory      $segmentsFactory
     * @param \Licentia\Reports\Model\Search\StatsFactory $statsFactory
     * @param \Magento\Catalog\Model\ProductFactory       $productFactory
     * @param \Magento\Backend\Block\Template\Context     $context
     * @param array                                       $data
     */
    public function __construct(
        \Licentia\Equity\Model\SegmentsFactory $segmentsFactory,
        \Licentia\Reports\Model\Search\StatsFactory $statsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {

        parent::__construct($context, $data);

        $this->searchStatsFactory = $statsFactory;
        $this->productFactory = $productFactory;
        $this->segmentsFactory = $segmentsFactory;

        $this->setTemplate('Licentia_Reports::searches/info.phtml');
    }

    /**
     * @return array
     */
    public function getGroups()
    {

        $blocks = \Licentia\Reports\Model\Search\Stats::getGroups();
        $blocks['single'] = 'Query Totals';

        return $blocks;
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

        return $this->searchStatsFactory->create()->getTypes();
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
