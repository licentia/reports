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

namespace Licentia\Reports\Block\Adminhtml\Stats;

/**
 * Adminhtml Campaigns grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Licentia\Panda\Model\ResourceModel\Campaigns\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Licentia\Reports\Model\Sales\Orders
     */
    protected $salesorder;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Grid constructor.
     *
     * @param \Magento\Framework\Registry                                         $registry
     * @param \Licentia\Reports\Model\Sales\Orders                                $salesorder
     * @param \Magento\Backend\Block\Template\Context                             $context
     * @param \Magento\Backend\Helper\Data                                        $backendHelper
     * @param \Licentia\Reports\Model\ResourceModel\Sales\Stats\CollectionFactory $collectionFactory
     * @param array                                                               $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Licentia\Reports\Model\Sales\Orders $salesorder,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Licentia\Reports\Model\ResourceModel\Sales\Stats\CollectionFactory $collectionFactory,
        array $data = []
    ) {

        $this->salesorder = $salesorder;
        $this->collectionFactory = $collectionFactory;
        $this->registry = $registry;
        parent::__construct($context, $backendHelper, $data);
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

        $columns = array_keys($this->getColumns());

        $collection = $this->getCollection();

        $select = $collection->getSelect();

        foreach ($columns as $column) {
            if (stripos($column, 'total') === false && stripos($column, 'count') === false) {
                continue;
            }

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

        /* @var \Licentia\Panda\Model\ResourceModel\Campaigns\Collection $collection */
        $collection = $this->collectionFactory->create();

        $type = $this->getType();

        $group = $this->getGroup();

        $mainTable = 'panda_sales_stats_' . $type;

        $collection->getSelect()
                   ->reset('from')
                   ->from(['main_table' => $mainTable]);

        $segmentId = $this->getRequest()->getParam('segment_id');

        if ($segmentId) {
            $collection->getSelect()->where('segment_id=?', $segmentId);
        } else {
            $collection->getSelect()->where('segment_id IS NULL');
        }

        if ($type != 'global' && $group != 'single') {
            $collection->getSelect()->group($type);
        }

        if ($group != 'single') {
            $collection->getSelect()->group($group);
        }

        $intervalStart = $this->getRequest()->getParam('interval_start');
        $intervalEnd = $this->getRequest()->getParam('interval_end');

        if ($intervalStart) {
            $collection->getSelect()->where('date >=?', $intervalStart);
        }
        if ($intervalEnd) {
            $collection->getSelect()->where('date <=?', $intervalEnd);
        }

        $columns = [
            'item_id'                      => 'item_id',
            'segment_id'                   => 'segment_id',
            'date'                         => 'date',
            'day'                          => 'day',
            'weekday'                      => 'weekday',
            'day_year'                     => 'day_year',
            'year'                         => 'year',
            'month'                        => 'month',
            'orders_count'                 => new \Zend_Db_Expr('SUM(orders_count)'),
            'total_qty_ordered'            => new \Zend_Db_Expr('SUM(total_qty_ordered)'),
            'total_qty_invoiced'           => new \Zend_Db_Expr('SUM(total_qty_invoiced)'),
            'total_income_amount'          => new \Zend_Db_Expr('SUM(total_income_amount)'),
            'total_revenue_amount'         => new \Zend_Db_Expr('SUM(total_revenue_amount)'),
            'total_profit_amount'          => new \Zend_Db_Expr('SUM(total_profit_amount)'),
            'total_invoiced_amount'        => new \Zend_Db_Expr('SUM(total_invoiced_amount)'),
            'total_canceled_amount'        => new \Zend_Db_Expr('SUM(total_canceled_amount)'),
            'total_paid_amount'            => new \Zend_Db_Expr('SUM(total_paid_amount)'),
            'total_refunded_amount'        => new \Zend_Db_Expr('SUM(total_refunded_amount)'),
            'total_tax_amount'             => new \Zend_Db_Expr('SUM(total_tax_amount)'),
            'total_tax_amount_actual'      => new \Zend_Db_Expr('SUM(total_tax_amount_actual)'),
            'total_shipping_amount'        => new \Zend_Db_Expr('SUM(total_shipping_amount)'),
            'total_shipping_amount_actual' => new \Zend_Db_Expr('SUM(total_shipping_amount_actual)'),
            'total_discount_amount'        => new \Zend_Db_Expr('SUM(total_discount_amount)'),
            'total_discount_amount_actual' => new \Zend_Db_Expr('SUM(total_discount_amount_actual)'),
        ];

        if ($type != 'global') {
            $columns[$type] = $type;
        }

        $collection->getSelect()->columns($columns);

        $this->setCollection($collection);

        #$GLOBALS['orders_grid'] = $collection;
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

        $groups = \Licentia\Reports\Model\Sales\Orders::getGroups();
        $groups['single'] = 'Single Record';

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

        $types = $this->salesorder->getTypes();

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
            'orders_count',
            [
                'header' => __('Orders N'),
                'index'  => 'orders_count',
                'type'   => 'number',

            ]
        );

        $this->addColumn(
            'total_qty_ordered',
            [
                'header'           => __('Sales Items'),
                'index'            => 'total_qty_ordered',
                'type'             => 'number',
                'header_css_class' => 'col-sales-items',
                'column_css_class' => 'col-sales-items',
            ]
        );

        $this->addColumn(
            'total_qty_invoiced',
            [
                'header'           => __('Items Invoiced'),
                'index'            => 'total_qty_invoiced',
                'type'             => 'number',
                'header_css_class' => 'col-items',
                'column_css_class' => 'col-items',
            ]
        );

        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);

        $this->addColumn(
            'total_income_amount',
            [
                'header'           => __('Orders Amount'),
                'type'             => 'currency',
                'currency_code'    => $currencyCode,
                'index'            => 'total_income_amount',
                'rate'             => $rate,
                'header_css_class' => 'col-sales-total',
                'column_css_class' => 'col-sales-total',
            ]
        );

        $this->addColumn(
            'total_revenue_amount',
            [
                'header'           => __('Revenue'),
                'type'             => 'currency',
                'currency_code'    => $currencyCode,
                'index'            => 'total_revenue_amount',
                'rate'             => $rate,
                'header_css_class' => 'col-revenue',
                'column_css_class' => 'col-revenue',
            ]
        );

        $this->addColumn(
            'total_profit_amount',
            [
                'header'           => __('Profit'),
                'type'             => 'currency',
                'currency_code'    => $currencyCode,
                'index'            => 'total_profit_amount',
                'rate'             => $rate,
                'header_css_class' => 'col-profit',
                'column_css_class' => 'col-profit',
            ]
        );

        $this->addColumn(
            'total_invoiced_amount',
            [
                'header'           => __('Invoiced'),
                'type'             => 'currency',
                'currency_code'    => $currencyCode,
                'index'            => 'total_invoiced_amount',
                'rate'             => $rate,
                'header_css_class' => 'col-invoiced',
                'column_css_class' => 'col-invoiced',
            ]
        );

        $this->addColumn(
            'total_paid_amount',
            [
                'header'           => __('Paid'),
                'type'             => 'currency',
                'currency_code'    => $currencyCode,
                'index'            => 'total_paid_amount',
                'rate'             => $rate,
                'header_css_class' => 'col-paid',
                'column_css_class' => 'col-paid',
            ]
        );

        $this->addColumn(
            'total_refunded_amount',
            [
                'header'           => __('Refunded'),
                'type'             => 'currency',
                'currency_code'    => $currencyCode,
                'index'            => 'total_refunded_amount',
                'rate'             => $rate,
                'header_css_class' => 'col-refunded',
                'column_css_class' => 'col-refunded',
            ]
        );

        /*
        $this->addColumn(
            'total_tax_amount',
            [
                'header'           => __('Sales Tax'),
                'type'             => 'currency',
                'currency_code'    => $currencyCode,
                'index'            => 'total_tax_amount',kpi.
                'rate'             => $rate,
                'header_css_class' => 'col-sales-tax',
                'column_css_class' => 'col-sales-tax',
            ]
        );
        */

        $this->addColumn(
            'total_tax_amount_actual',
            [
                'header'            => __('Tax'),
                'type'              => 'currency',
                'currency_code'     => $currencyCode,
                'index'             => 'total_tax_amount_actual',
                'total'             => 'sum',
                'visibility_filter' => ['show_actual_columns'],
                'rate'              => $rate,
                'header_css_class'  => 'col-tax',
                'column_css_class'  => 'col-tax',
            ]
        );

        /*
                $this->addColumn(
                    'total_shipping_amount',
                    [
                        'header'           => __('Sales Shipping'),
                        'type'             => 'currency',
                        'currency_code'    => $currencyCode,
                        'index'            => 'total_shipping_amount',
                        'total'            => 'sum',
                        'rate'             => $rate,
                        'header_css_class' => 'col-sales-shipping',
                        'column_css_class' => 'col-sales-shipping',
                    ]
                );
          */
        $this->addColumn(
            'total_shipping_amount_actual',
            [
                'header'           => __('Shipping'),
                'type'             => 'currency',
                'currency_code'    => $currencyCode,
                'index'            => 'total_shipping_amount_actual',
                'rate'             => $rate,
                'header_css_class' => 'col-shipping',
                'column_css_class' => 'col-shipping',
            ]
        );

        /*
        $this->addColumn(
            'total_discount_amount',
            [
                'header'           => __('Sales Discount'),
                'type'             => 'currency',
                'currency_code'    => $currencyCode,
                'index'            => 'total_discount_amount',kpi.
                'rate'             => $rate,
                'header_css_class' => 'col-sales-discount',
                'column_css_class' => 'col-sales-discount',
            ]
        );

        */
        $this->addColumn(
            'total_discount_amount_actual',
            [
                'header'           => __('Discount'),
                'type'             => 'currency',
                'currency_code'    => $currencyCode,
                'index'            => 'total_discount_amount_actual',
                'rate'             => $rate,
                'header_css_class' => 'col-discount',
                'column_css_class' => 'col-discount',
            ]
        );

        $this->addColumn(
            'total_canceled_amount',
            [
                'header'           => __('Canceled'),
                'type'             => 'currency',
                'currency_code'    => $currencyCode,
                'index'            => 'total_canceled_amount',
                'rate'             => $rate,
                'header_css_class' => 'col-canceled',
                'column_css_class' => 'col-canceled',
            ]
        );

        if (!$this->registry->registry('panda_orders_grid')) {
            $this->addExportType('*/*/exportCsv', __('CSV'));
            $this->addExportType('*/*/exportXml', __('Excel XML'));
        }

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

        $mainTable = 'panda_sales_stats_' . $type;

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

        $mainTable = 'panda_sales_stats_' . $type;

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
