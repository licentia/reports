<?php

/**
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
 * Class Grid
 *
 * @package Licentia\Reports\Block\Adminhtml\Searches
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Licentia\Panda\Model\ResourceModel\Campaigns\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Licentia\Reports\Model\Sales\StatsFactory
     */
    protected $salesStats;

    /**
     * Grid constructor.
     *
     * @param \Licentia\Reports\Model\Sales\StatsFactory                          $statsFactory
     * @param \Magento\Framework\Registry                                         $registry
     * @param \Magento\Backend\Block\Template\Context                             $context
     * @param \Magento\Backend\Helper\Data                                        $backendHelper
     * @param \Licentia\Reports\Model\ResourceModel\Sales\Stats\CollectionFactory $collectionFactory
     * @param array                                                               $data
     */
    public function __construct(
        \Licentia\Reports\Model\Sales\StatsFactory $statsFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Licentia\Reports\Model\ResourceModel\Sales\Stats\CollectionFactory $collectionFactory,
        array $data = []
    ) {

        parent::__construct($context, $backendHelper, $data);

        $this->registry = $registry;
        $this->collectionFactory = $collectionFactory;
        $this->salesStats = $statsFactory;
    }

    protected function _construct()
    {

        parent::_construct();
        $this->setId('pandaSplitsGrid');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this|\Magento\Backend\Block\Widget\Grid\Extended
     */
    public function _prepareGrid()
    {

        parent::_prepareGrid();

        $columns = ['total'];

        $collection = $this->getCollection();

        $select = $collection->getSelect();

        foreach ($columns as $column) {
            $select->reset('columns');
            $select->reset('group');
            $select->reset('limitcount');
            $select->reset('limitoffset');
            $tmpS = $select->columns(['total' => new \Zend_Db_Expr("SUM($column)")]);
            $columns[$column] = $collection->getResource()
                                           ->getConnection()->fetchOne($tmpS);
        }

        $this->setTotals(new \Magento\Framework\DataObject($columns));
        $this->setCountTotals(true);

        return $this;
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {

        /* @var $collection \Licentia\Panda\Model\ResourceModel\Campaigns\Collection */
        $collection = $this->collectionFactory->create();

        $type = $this->getType();

        $group = $this->getGroup();

        $mainTable = 'panda_search_performance_' . $type;

        $collection->getSelect()
                   ->reset('from')
                   ->from(['main_table' => $mainTable]);

        $segmentdId = $this->getRequest()->getParam('segment_id');

        if ($segmentdId) {
            $collection->getSelect()->where('segment_id=?', $segmentdId);
        } else {
            $collection->getSelect()->where('segment_id IS NULL');
        }

        if ($type != 'global' && $type != 'female' && $type != 'male') {
            $collection->getSelect()->group($type);
        }

        $collection->getSelect()
                   ->group($group)
                   ->group('query');

        $intervalStart = $this->getRequest()->getParam('interval_start');
        $intervalEnd = $this->getRequest()->getParam('interval_end');

        if ($intervalStart) {
            $collection->getSelect()->where('date >=?', $intervalStart);
        }
        if ($intervalEnd) {
            $collection->getSelect()->where('date <=?', $intervalEnd);
        }
        $columns = [
            'item_id'    => 'item_id',
            'segment_id' => 'segment_id',
            'date'       => 'date',
            'day'        => 'day',
            'weekday'    => 'weekday',
            'day_year'   => 'day_year',
            'year'       => 'year',
            'month'      => 'month',
            'query'      => 'query',
            'total'      => new \Zend_Db_Expr('SUM(total)'),
        ];

        if ($type != 'global' && $type != 'female' && $type != 'male') {
            $columns[$type] = $type;
        }

        if ($group == 'single') {
            $collection->getSelect()->reset('group')->group('query');

            if ($type != 'global' && $type != 'female' && $type != 'male') {
                $collection->getSelect()->group($type);
            }
        }

        $collection->getSelect()
                   ->reset('columns')
                   ->columns($columns);

        $this->setCollection($collection);

        $this->registry->register('panda_orders_grid', $collection, true);

        return parent::_prepareCollection();
    }

    /**
     * @return mixed|string
     */
    /**
     * @return mixed|string
     */
    public function getGroup()
    {

        $groups = \Licentia\Reports\Model\Search\Stats::getGroups();
        $groups['single'] = 'Query';

        $group = $this->getRequest()->getParam('group_results');

        if (!array_key_exists($group, $groups)) {
            $group = 'month';
        }

        return $group;
    }

    /**
     * @return mixed|string
     */
    /**
     * @return mixed|string
     */
    public function getType()
    {

        $types = $this->salesStats->create()->getTypes();
        $type = $this->getRequest()->getParam('type');

        if (!array_key_exists($type, $types)) {
            $type = 'global';
        }

        return $type;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {

        $group = $this->getGroup();

        if ($group == 'month' || $group == 'year_month') {
            $this->addColumn(
                'period_month',
                [
                    'header'           => __('Month'),
                    'index'            => 'month',
                    'totals_label'     => __('Total'),
                    'type'             => 'options',
                    'options'          => $this->getMonths(),
                    'header_css_class' => 'col-period',
                    'column_css_class' => 'col-period',
                ]
            );
        }

        if ($group == 'year_month' || $group == 'year') {
            $this->addColumn(
                'period_year',
                [
                    'header'           => __('Year'),
                    'index'            => 'year',
                    'type'             => 'options',
                    'options'          => $this->getYears(),
                    'totals_label'     => __('Total'),
                    'header_css_class' => 'col-period',
                    'column_css_class' => 'col-period',
                ]
            );
        }

        if ($group == 'day') {
            $this->addColumn(
                'period_day',
                [
                    'header'           => __('Day'),
                    'index'            => 'day',
                    'type'             => 'options',
                    'options'          => array_combine(range(1, 31), range(1, 31)),
                    'totals_label'     => __('Total'),
                    'header_css_class' => 'col-period',
                    'column_css_class' => 'col-period',
                ]
            );
        }

        if ($group == 'date') {
            $this->addColumn(
                'period_date',
                [
                    'header'           => __('Date'),
                    'index'            => 'date',
                    'type'             => 'date',
                    'totals_label'     => __('Total'),
                    'header_css_class' => 'col-period',
                    'column_css_class' => 'col-period',
                ]
            );
        }
        if ($group == 'weekday') {
            $this->addColumn(
                'period_weekday',
                [
                    'header'           => __('Week Day'),
                    'index'            => 'weekday',
                    'type'             => 'options',
                    'options'          => $this->getWeekDays(),
                    'totals_label'     => __('Total'),
                    'header_css_class' => 'col-period',
                    'column_css_class' => 'col-period',
                ]
            );
        }

        if ($this->getType() == 'country' || $this->getType() == 'region') {
            $this->addColumn(
                'country',
                [
                    'header' => __('Country'),
                    'index'  => 'country',
                    'type'   => 'country',

                ]
            );
        }

        if ($this->getType() == 'region') {
            $this->addColumn(
                'region',
                [
                    'header' => __('Region'),
                    'index'  => 'region',

                ]
            );
        }

        if ($this->getType() == 'age') {
            $this->addColumn(
                'age',
                [
                    'header' => __('Age'),
                    'index'  => 'age',
                    'type'   => 'text',
                ]
            );
        }

        if ($this->getType() == 'gender') {
            $this->addColumn(
                'gender',
                [
                    'header'  => __('Gender'),
                    'index'   => 'gender',
                    'type'    => 'options',
                    'options' => ['male' => __('Male'), 'female' => __('Female')],
                ]
            );
        }

        $this->addColumn(
            'query',
            [
                'header' => __('Query'),
                'index'  => 'query',
            ]
        );

        $this->addColumn(
            'total',
            [
                'header' => __('total'),
                'index'  => 'total',
                'type'   => 'number',
            ]
        );

        # $this->addExportType('*/*/exportCsv', __('CSV'));
        # $this->addExportType('*/*/exportExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {

        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * @return array
     */
    public function getMonths()
    {

        $resource = $this->collectionFactory->create()->getResource();

        $type = $this->getType();

        $mainTable = 'panda_search_performance_' . $type;

        $result = $resource->getConnection()
                           ->fetchCol(
                               $resource->getConnection()
                                        ->select()
                                        ->from($mainTable, ['DISTINCT(month)'])
                           );

        $months = array_combine($result, $result);

        foreach ($months as $key => $month) {
            $dt = \DateTime::createFromFormat('!m', $month);

            $months[$key] = $dt->format('F');
        }

        return $months;
    }

    /**
     * @return array
     */
    public function getYears()
    {

        $resource = $this->collectionFactory->create()->getResource();

        $type = $this->getType();

        $mainTable = 'panda_search_performance_' . $type;

        $result = $resource->getConnection()
                           ->fetchCol(
                               $resource->getConnection()
                                        ->select()
                                        ->from($mainTable, ['DISTINCT(year)'])
                           );

        return array_combine($result, $result);
    }

    /**
     * @return array
     */
    public function getWeekDays()
    {

        return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    }
}
