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

namespace Licentia\Reports\Model\Sales;

use Licentia\Reports\Model\Indexer;
use Magento\Store\Model\ScopeInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Stats
 *
 * @package Licentia\Panda\Model\Sales
 */
class Stats extends \Magento\Framework\Model\AbstractModel
{

    /**
     *
     */
    const PERFORMANCE_TABLE_PREFIX = 'panda_products_performance_';

    /**
     *
     */
    const FIELDS_SQL_EXP = [
        'sale_price'                    => 'AVG(sale_price)',
        'sale_price_discount'           => 'AVG(sale_price_discount)',
        'row_total'                     => 'SUM(row_total)',
        'row_total_discount'            => 'SUM(row_total_discount)',
        'row_total_global'              => 'SUM(row_total_global)',
        'row_total_discount_percentage' => 'AVG(row_total_discount_percentage)',
        'unit_price'                    => 'AVG(unit_price)',
        'profit'                        => 'SUM(profit)',
        'cost'                          => 'SUM(cost)',
        'taxes'                         => 'SUM(taxes)',
        'qty'                           => 'SUM(qty)',
        'qty_discount'                  => 'SUM(qty_discount)',
        'qty_global'                    => 'SUM(qty_global)',
    ];

    /**
     *
     */
    const AVAILABLE_FIELDS_TO_FILTER = [
        'sale_price'                    => 'Sale Price',
        'sale_price_discount'           => 'Sale Price w/ Discount',
        'row_total'                     => 'Row Total',
        'row_total_discount'            => 'Row Total w/ Discount',
        'row_total_global'              => 'Row Total Global',
        'row_total_discount_percentage' => 'Row Total Discount %',
        'unit_price'                    => 'Unit Price',
        'profit'                        => 'Profit',
        'cost'                          => 'Cost',
        'taxes'                         => 'Taxes',
        'qty'                           => 'Qty',
        'qty_discount'                  => 'Qty w/ Discount',
        'qty_global'                    => 'Qty Global',
    ];

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'panda_products_performance';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'panda_products_performance';

    /**
     * @var \Licentia\Equity\Model\SegmentsFactory
     */
    protected $segmentsFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected $pandaHelper;

    /**
     * @var
     */
    protected $describeTable;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Licentia\Reports\Model\ResourceModel\Sales\StatsFactory
     */
    protected $statsFactory;

    /**
     * @var \Licentia\Reports\Model\IndexerFactory
     */
    protected $indexer;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_init(\Licentia\Reports\Model\ResourceModel\Sales\Stats::class);
    }

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Stats constructor.
     *
     * @param \Licentia\Reports\Model\IndexerFactory                       $indexer
     * @param \Licentia\Reports\Helper\Data                                $helper
     * @param \Licentia\Reports\Model\ResourceModel\Sales\StatsFactory     $statsResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface           $scopeConfigInterface
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface         $timezone
     * @param \Magento\Catalog\Model\ProductFactory                        $productFactory
     * @param \Licentia\Equity\Model\SegmentsFactory                       $segmentsFactory
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Licentia\Reports\Model\IndexerFactory $indexer,
        \Licentia\Reports\Helper\Data $helper,
        \Licentia\Reports\Model\ResourceModel\Sales\StatsFactory $statsResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Licentia\Equity\Model\SegmentsFactory $segmentsFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->indexer = $indexer->create();
        $this->segmentsFactory = $segmentsFactory;
        $this->timezone = $timezone;
        $this->productFactory = $productFactory;
        $this->scopeConfig = $scopeConfigInterface;
        $this->statsFactory = $statsResource->create();
        $this->pandaHelper = $helper;

        $this->pandaHelper->getConnection()
                          ->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

    }

    /**
     * @return string
     */
    public function getMySQLVersion()
    {

        return $this->pandaHelper->getConnection()->fetchOne('SELECT version()');
    }

    /**
     * @param        $skus
     * @param string $type
     * @param string $group
     *
     * @param string $attributeCode
     *
     * @return array
     */
    public function getPeriodsInCollection($skus, $type = 'global', $group = 'date', $attributeCode = '')
    {

        $resource = $this->statsFactory;
        $connection = $resource->getConnection();

        $tableName = $resource->getTable(self::PERFORMANCE_TABLE_PREFIX . $type);

        $select = $connection->select()->from($tableName, []);

        $order = $group;
        $distinct = $group;

        if ($group == 'year_month') {
            $select->order('year');
            $select->order('month');

            $distinct = "CONCAT(year,'-',month)";
        } else {
            $select->order($order);
        }

        $select->columns(
            [
                'distinct' => new \Zend_Db_Expr("DISTINCT($distinct)"),
            ]
        );

        if ($type != 'attribute') {
            $select->where('sku IN (?)', $skus);
        }

        if ($type == 'attribute') {
            $select->where('attribute_code=?', $attributeCode);
        }

        $days = $connection->fetchCol($select);

        return $days;
    }

    /**
     * @param        $skus
     * @param string $type
     * @param string $field
     * @param string $attributeCode
     * @param null   $attributeValue
     *
     * @return array
     */
    public function getAgesInCollection(
        $skus,
        $type = 'global',
        $field = 'age',
        $attributeCode = '',
        $attributeValue = null
    ) {

        $resource = $this->statsFactory;
        $connection = $resource->getConnection();

        if ($attributeValue) {
            $field = 'attribute_2';
        }

        $tableName = $resource->getTable(self::PERFORMANCE_TABLE_PREFIX . $type);

        $select = $connection->select()
                             ->from($tableName, [])
                             ->columns(
                                 [
                                     'distinct' => new \Zend_Db_Expr("DISTINCT($field)"),
                                 ]
                             )
                             ->limit(25)
                             ->order($field);

        if ($type != 'attribute') {
            $select->where('sku IN (?)', $skus);
        }

        if ($type == 'attribute') {
            $select->where('attribute_code=?', $attributeCode);
        }

        if ($attributeValue) {
            $select->where('attribute=?', $attributeValue);
        }

        $days = $connection->fetchCol($select);

        array_unshift($days, 0);

        return $days;
    }

    /**
     * @param        $skus
     * @param string $type
     * @param string $group
     * @param null   $segmentId
     * @param bool   $intervalStart
     * @param bool   $intervalEnd
     * @param string $attributeCode
     * @param null   $attributeValue
     * @param string $attributeCode2
     *
     * @return array
     */
    public function getStatsCollection(
        $skus,
        $type = 'global',
        $group = 'date',
        $segmentId = null,
        $intervalStart = false,
        $intervalEnd = false,
        $attributeCode = '',
        $attributeValue = null,
        $attributeCode2 = ''
    ) {

        $collection = [];

        $resource = $this->statsFactory;
        $connection = $resource->getConnection();

        $tableName = $resource->getTable(self::PERFORMANCE_TABLE_PREFIX . $type);

        $order = $group;

        $days = $this->getPeriodsInCollection($skus, $type, $group, $attributeCode);

        if ($group == 'year_month') {
            $order = 'month';
        }

        $auxFields = ['age', 'country', 'region', 'gender', 'attribute'];

        if (in_array($type, $auxFields)) {
            $ages = $this->getAgesInCollection($skus, $type, $type, $attributeCode, $attributeValue);
        } else {
            $ages = [0];
        }

        $columns = [
            'segment_id'                             => 'segment_id',
            'date'                                   => 'date',
            'weekday'                                => 'weekday',
            'day_year'                               => 'day_year',
            'year'                                   => 'year',
            'day'                                    => 'day',
            'month'                                  => 'month',
            'sku'                                    => 'sku',
            'sale_price'                             => new \Zend_Db_Expr(
                'ROUND(SUM(sale_price*qty_global)/SUM(qty_global),4)'
            ),
            'sale_price_discount'                    => new \Zend_Db_Expr(
                'ROUND(SUM(sale_price_discount*qty_discount)/SUM(qty_discount),4)'
            ),
            'qty'                                    => new \Zend_Db_Expr('SUM(qty)'),
            'qty_discount'                           => new \Zend_Db_Expr('SUM(qty_discount)'),
            'qty_global'                             => new \Zend_Db_Expr('SUM(qty_global)'),
            'row_total'                              => new \Zend_Db_Expr('SUM(row_total)'),
            'row_total_discount'                     => new \Zend_Db_Expr('SUM(row_total_discount)'),
            'row_total_global'                       => new \Zend_Db_Expr('SUM(row_total_global)'),
            'row_total_discount_percentage'          => new \Zend_Db_Expr('AVG(row_total_discount_percentage)'),
            'unit_price'                             => new \Zend_Db_Expr('AVG(unit_price)'),
            'profit'                                 => new \Zend_Db_Expr('SUM(profit)'),
            'cost'                                   => new \Zend_Db_Expr('SUM(cost)'),
            'taxes'                                  => new \Zend_Db_Expr('SUM(taxes)'),
            'previous_sale_price'                    => new \Zend_Db_Expr(
                'ROUND(SUM(previous_sale_price*previous_qty_global)/SUM(previous_qty_global),4)'
            ),
            'previous_sale_price_discount'           => new \Zend_Db_Expr(
                'ROUND(SUM(previous_sale_price_discount*previous_qty_discount)/SUM(previous_qty_discount),4)'
            ),
            'previous_qty'                           => new \Zend_Db_Expr('SUM(previous_qty)'),
            'previous_qty_discount'                  => new \Zend_Db_Expr('SUM(previous_qty_discount)'),
            'previous_qty_global'                    => new \Zend_Db_Expr('SUM(previous_qty_global)'),
            'previous_row_total'                     => new \Zend_Db_Expr('SUM(previous_row_total)'),
            'previous_row_total_discount'            => new \Zend_Db_Expr('SUM(previous_row_total_discount)'),
            'previous_row_total_global'              => new \Zend_Db_Expr('SUM(previous_row_total_global)'),
            'previous_row_total_discount_percentage' => new \Zend_Db_Expr(
                'AVG(previous_row_total_discount_percentage)'
            ),
            'previous_unit_price'                    => new \Zend_Db_Expr('AVG(previous_unit_price)'),
            'previous_profit'                        => new \Zend_Db_Expr('SUM(previous_profit)'),
            'previous_cost'                          => new \Zend_Db_Expr('SUM(previous_cost)'),
            'previous_taxes'                         => new \Zend_Db_Expr('SUM(previous_taxes)'),
        ];

        if ($type != 'global') {
            $columns[$type] = $type;
        }
        $noResults = true;
        foreach ($skus as $sku) {
            foreach ($ages as $age) {
                $select = $connection->select()
                                     ->from($tableName, $columns)
                                     ->order($order);

                if ($type != 'attribute') {
                    $select->where('sku=?', (string) $sku);
                }

                if (in_array($type, $auxFields) && $age !== 0) {
                    $fieldSelect = $type;

                    if ($attributeCode2) {
                        $fieldSelect = 'attribute_2';
                    }

                    $select->where($fieldSelect . '=?', $age);
                }

                if (in_array($type, $auxFields) && $age === 0) {
                    $select = $connection->select()
                                         ->from($tableName)
                                         ->columns(self::FIELDS_SQL_EXP)
                                         ->group($order)
                                         ->order($order);

                    if ($type != 'attribute') {
                        $select->where('sku=?', (string) $sku);
                    }
                }

                if ($segmentId) {
                    $select->where('segment_id=?', $segmentId);
                } else {
                    $select->where('segment_id IS NULL');
                }

                if ($group == 'year_month') {
                    $select->reset('order');
                    $select->order('year');
                    $select->order('month');
                    $select->group('year');
                    $select->group('month');
                } else {
                    $select->group($group);
                }

                if ($intervalStart) {
                    $select->where('date >=?', $intervalStart);
                }
                if ($intervalEnd) {
                    $select->where('date <=?', $intervalEnd);
                }

                if ($type == 'attribute') {
                    $select->where('attribute_code=?', $attributeCode);
                }

                if ($attributeCode2) {
                    $select->where('attribute=?', $attributeValue);
                    if ($age > 0) {
                        $select->group('attribute_2');
                        $select->order('attribute_2');
                    }
                }

                $collection[$sku][$age] = $connection->fetchAll($select);

                if (count($collection[$sku][$age]) > 0) {
                    $noResults = false;
                }
            }

            if ($noResults) {
                return [];
            }

            foreach ($ages as $age) {
                foreach ($collection[$sku][$age] as $key => $value) {
                    if ($group == 'year_month') {
                        $valueOrder = $value['year'] . '-' . $value['month'];
                    } else {
                        $valueOrder = $value[$order];
                    }

                    unset($collection[$sku][$age][$key]);

                    $collection[$sku][$valueOrder][$age] = $value;
                }
            }

            foreach ($days as $day) {
                foreach ($ages as $age) {
                    if (!array_key_exists($day, $collection[$sku])) {
                        $tmp = array_combine(array_keys($value), array_fill_keys(array_keys($value), '-'));
                        $collection[$sku][$day][$age] = $tmp;
                    }

                    if (!array_key_exists($age, $collection[$sku][$day])) {
                        $tmp = array_combine(array_keys($value), array_fill_keys(array_keys($value), '-'));
                        $collection[$sku][$day][$age] = $tmp;
                    }
                }
            }
        }

        foreach ($skus as $sku) {
            foreach ($ages as $age) {
                if (array_key_exists($age, $collection[$sku]) && !in_array($age, $days)) {
                    unset($collection[$sku][$age]);
                }
            }
        }

        return $collection;
    }

    /**
     * @return Stats
     */
    public function rebuildForYesterday()
    {

        $date = $this->timezone->date($this->pandaHelper->gmtDate())
                               ->sub(new \DateInterval('P1D'))
                               ->format('Y-m-d');

        return $this->rebuildAll($date);
    }

    /**
     * @return Stats
     */
    public function reindexPerformance()
    {

        return $this->rebuildAll();
    }

    /**
     * @param bool $date
     *
     * @return $this
     */
    public function rebuildAll($date = false)
    {

        $segments = $this->segmentsFactory->create()
                                          ->getCollection()
                                          ->addFieldToFilter('products_relations', 1);

        $types = $this->getTypes();

        if (!$date) {
            $types = $this->indexer->getTypesToReindex($types, 'performance');
            $this->indexer->updateIndexStatus(Indexer::STATUS_WORKING, 'performance');
        }

        foreach ($types as $keyType => $type) {
            $this->rebuildItem($keyType, null, $date);

            /** @var \Licentia\Equity\Model\Segments $segment */
            foreach ($segments as $segment) {
                $this->rebuildItem($keyType, $segment->getId(), $date);
            }

            if (!$date) {
                $this->indexer->updateIndex($type, 0, 'performance');
            }
        }

        if (!$date) {
            $this->indexer->updateIndexStatus(Indexer::STATUS_VALID, 'performance');
        }

        return $this;
    }

    /**
     * @param string $type
     * @param null   $segmentId
     * @param bool   $date
     *
     * @return $this
     * @throws \Exception
     */
    public function rebuildItem($type, $segmentId = null, $date = false)
    {

        $type = strtolower($type);

        if (!array_key_exists($type, $this->getTypes())) {
            throw new \Exception('Invalid Type:' . $type);
        }

        /** @var \Licentia\Reports\Model\ResourceModel\Sales\Stats $resource */
        $resource = $this->statsFactory;
        $connection = $resource->getConnection();

        $nextField = false;

        if ($type == 'country') {
            $nextField = 'country';
        }

        if ($type == 'region') {
            $nextField = 'region';
        }

        if ($type == 'age') {
            $nextField = 'age_one';
        }

        if ($type == 'gender') {
            $nextField = 'gender';
        }

        $salesInvoiceItemTable = $resource->getTable('sales_invoice_item');
        $salesInvoiceTable = $resource->getTable('sales_invoice');
        $salesOrderTable = $resource->getTable('sales_order');
        $pandaCustomerKpisTable = $resource->getTable('panda_customers_kpis');
        $pandaSegmentsRecordsTable = $resource->getTable('panda_segments_records');
        $pandaSalesAddressTable = $resource->getTable('sales_order_address');

        if ($type == 'attribute') {
            $loopAttrs = explode(
                ',',
                $this->scopeConfig->getValue('panda_equity/reports/attributes', ScopeInterface::SCOPE_WEBSITE)
            );
            $loopAttrsSec = explode(
                ',',
                $this->scopeConfig->getValue('panda_equity/reports/attributes', ScopeInterface::SCOPE_WEBSITE)
            );
        } else {
            $loopAttrs = [0];
            $loopAttrsSec = [0];
        }

        if (!$this->shouldWeRebuildForType($type)) {
            if ($output = $this->getData('consoleOutput')) {
                if ($output instanceof OutputInterface) {
                    $extra = '';
                    if ($segmentId) {
                        $extra .= ' / SEG ID:' . $segmentId;
                    }

                    $extra .= ' - ' . $this->pandaHelper->gmtDate('Y-m-d H:i:s');

                    $output->writeln("Performance | Finished (No Ages): " . $type . " / " . $extra);
                }
            }

            return $this;
        }

        foreach ($loopAttrs as $attributeCode) {
            if ($type == 'attribute') {
                $loopAttrs2 = $this->getAttributeOptions($attributeCode);
            } else {
                $loopAttrs2 = [0];
            }

            foreach ($loopAttrs2 as $attributeValue) {
                foreach ($loopAttrsSec as $attributeCodeSecondary) {
                    if ($type == 'attribute' && $attributeCodeSecondary == $attributeCode) {
                        continue;
                    }

                    $select = $connection->select();

                    if ($type == 'attribute') {
                        $nextField = $attributeCode;
                    }

                    $selectColumns = [
                        'promotion'  => new \Zend_Db_Expr("IF(sii.base_discount_amount IS NULL, 0, 1)"),
                        'discount'   => new \Zend_Db_Expr(
                            "(SUM(IFNULL(sii.base_discount_amount,0)) / SUM(sii.qty)) * so.base_to_global_rate"
                        ),
                        'sale_price' => new \Zend_Db_Expr(
                            "(SUM(sii.base_row_total_incl_tax) - SUM(IFNULL(sii.base_discount_amount,0))) / " .
                            "SUM(sii.qty) * so.base_to_global_rate"
                        ),
                        'qty'        => new \Zend_Db_Expr("SUM(sii.qty)"),
                        'sku'        => new \Zend_Db_Expr("sii.sku"),
                        'row_total'  => new \Zend_Db_Expr(
                            "(SUM(sii.base_row_total) - SUM(IFNULL(sii.base_discount_amount,0))) * " .
                            "so.base_to_global_rate"
                        ),
                        'unit_price' => new \Zend_Db_Expr("AVG(base_price * so.base_to_global_rate)"),
                        'profit'     => new \Zend_Db_Expr(
                            "(SUM(sii.base_row_total) - SUM(IFNULL(sii.base_discount_amount,0)) - 
                    IFNULL(sii.base_cost  , 0) - 
                    IF(so.panda_shipping_cost IS NULL ,0 ,(SUM(so.panda_shipping_cost) / SUM(si.total_qty) * " .
                            "SUM(sii.qty))) * so.base_to_global_rate)"
                        ),
                        'cost'       => new \Zend_Db_Expr(
                            "(SUM(IFNULL(sii.base_discount_amount,0)) + IFNULL(sii.base_cost , 0) +
                                                    IF(
                                                        so.panda_shipping_cost IS NULL ,
                                                        0 ,
                                                        (
                                                           ( SUM(IFNULL(so.panda_shipping_cost,0)) + " .
                            "SUM(IFNULL(so.panda_extra_costs,0))) / SUM(si.total_qty) * SUM(sii.qty)
                                                        )
                                                    ) * so.base_to_global_rate)"
                        ),
                    ];

                    $selectColumns['taxes'] = new \Zend_Db_Expr(
                        sprintf(
                            'SUM((%s / %s + %s ) * %s)',
                            $connection->getIfNullSql('si.base_shipping_tax_amount', 0),
                            $connection->getIfNullSql('si.total_qty', 0),
                            $connection->getIfNullSql('sii.base_tax_amount', 0),
                            $connection->getIfNullSql('so.base_to_global_rate', 0)
                        )
                    );

                    $selectColumns['month'] = new \Zend_Db_Expr("MONTH(si.created_at)");
                    $selectColumns['year'] = new \Zend_Db_Expr("YEAR(si.created_at)");
                    $selectColumns['date'] = new \Zend_Db_Expr("date_format(si.created_at,'%Y-%m-%d')");
                    $selectColumns['day'] = new \Zend_Db_Expr("date_format(si.created_at,'%d')");
                    $selectColumns['weekday'] = new \Zend_Db_Expr("date_format(si.created_at,'%w')");
                    $selectColumns['day_year'] = new \Zend_Db_Expr("date_format(si.created_at,'%j')");

                    $mainTable = self::PERFORMANCE_TABLE_PREFIX . $type;

                    $mainTable = $resource->getTable($mainTable);

                    if ($type == 'country') {
                        $selectColumns['country'] = new \Zend_Db_Expr("addr.country_id");

                        $countries = [];
                        if ($this->scopeConfig->getValue(
                            'panda_equity/reports/country',
                            ScopeInterface::SCOPE_WEBSITE
                        )) {
                            $countries = array_filter(
                                explode(
                                    ',',
                                    $this->scopeConfig->getValue(
                                        'panda_equity/reports/country',
                                        ScopeInterface::SCOPE_WEBSITE
                                    )
                                )
                            );
                        }

                        if ($countries) {
                            $select->where(' addr.country_id IN (?)', $countries);
                        }
                    }

                    $extraField = false;
                    if ($type == 'region') {
                        $select->where(new \Zend_Db_Expr("LENGTH(addr.region)>0"));

                        $regions = array_filter(
                            explode(
                                ',',
                                $this->scopeConfig->getValue(
                                    'panda_equity/reports/region',
                                    ScopeInterface::SCOPE_WEBSITE
                                )
                            )
                        );

                        if ($regions) {
                            $select->where(' addr.region_id IN (?)', $regions);
                        }

                        $extraField = 'country';
                        $selectColumns['region'] = new \Zend_Db_Expr("addr.region");
                        $selectColumns['country'] = new \Zend_Db_Expr("addr.country_id");
                    }

                    if ($type == 'age') {
                        $selectColumns['age_one'] = new \Zend_Db_Expr(
                            \Licentia\Reports\Helper\Data::getAgeMySQLGroup($this->getMySQLVersion())
                        );
                    }

                    if ($type == 'gender') {
                        $selectColumns['gender'] = "kpi.gender";
                    }

                    $select->from(['sii' => $salesInvoiceItemTable], $selectColumns);

                    $select->join(['si' => $salesInvoiceTable], "si.entity_id = sii.parent_id", []);
                    $select->join(['so' => $salesOrderTable], "so.entity_id = si.order_id", []);

                    $select->join(
                        ['kpi' => $pandaCustomerKpisTable],
                        "kpi.email_meta = so.customer_email",
                        []
                    );

                    if ($segmentId) {
                        $select->join(
                            $pandaSegmentsRecordsTable,
                            "$pandaSegmentsRecordsTable.email = kpi.email_meta",
                            []
                        );
                    }

                    if ($type == 'age') {
                        $select->where(' kpi.age > ?', 18);
                    }

                    if ($type == 'gender') {
                        $select->where("kpi.gender IN (?)", ['male', 'female']);
                    }

                    if ($type == 'country' || $type == 'region') {
                        $select->join(
                            ['addr' => $pandaSalesAddressTable],
                            "addr.parent_id=so.entity_id AND addr.address_type='billing'",
                            []
                        );
                    }

                    if ($segmentId) {
                        $select->where("$pandaSegmentsRecordsTable.segment_id =?", $segmentId);
                    }

                    $select->group("IF(sii.base_discount_amount>0,1,0)");

                    if ($date) {
                        $select->where("DATE_FORMAT(si.created_at,'%Y-%m-%d')=?", $date);
                    }

                    if ($type != 'attribute') {
                        $select->group("sii.sku");
                    }

                    if ($type == 'country') {
                        $select->group("addr.country_id");
                    }

                    if ($type == 'region') {
                        $select->group("CONCAT(addr.region,'-',addr.country_id)");
                    }

                    if ($type == 'age') {
                        $select->group("age_one");
                    }

                    if ($type == 'attribute') {
                        $select->group($attributeCode);
                    }

                    if ($type == 'gender') {
                        $select->group("kpi.gender");
                    }

                    if ($type != 'attribute') {
                        $select->order("sii.sku");
                    }

                    $select->order($nextField);

                    $groupField = 'day';
                    $select->group("date_format(si.created_at , '%Y-%m-%d')");
                    $select->order("date_format(si.created_at , '%Y-%m-%d')");

                    if ($type == 'attribute') {
                        $select->join(['e' => $resource->getTable('catalog_product_entity')], "e.sku = sii.sku", []);
                        $col = $this->productFactory->create()
                                                    ->getCollection()
                                                    ->addAttributeToFilter($attributeCode, ['neq' => 100])
                                                    ->addAttributeToFilter($attributeCodeSecondary, ['neq' => 100]);

                        $joinsAttributes = $col->getSelect()->getPart('from');
                        unset($joinsAttributes['e']);

                        foreach ($joinsAttributes as $helpKey => $help) {
                            $select->where($helpKey . '.value IS NOT NULL');

                            $select->join(
                                [$helpKey => $help['tableName']],
                                $help['joinCondition'],
                                [str_replace('at_', '', $helpKey) => $helpKey . '.value']
                            );
                        }

                        $select->where('at_' . $attributeCode . '.value=?', $attributeValue);
                    }

                    $mainAttribute = 'sku';

                    if ($type == 'attribute') {
                        $mainAttribute = $attributeCode;
                    }

                    $select->order('promotion');
                    #$select->where('sii.sku=?', '1086');

                    $result = $connection->fetchAll($select);
                    $totalResults = count($result);

                    $data = [];
                    $history = [];
                    $skipNext = false;

                    for ($i = 0; $i < $totalResults; $i++) {
                        if ($skipNext) {
                            $skipNext = false;
                            continue;
                        }
                        if (isset($result[$i + 1])) {
                            $next = $result[$i + 1];
                        } else {
                            $next = false;
                        }

                        $current = $result[$i];

                        if ($next &&
                            $next[$groupField] == $current[$groupField] &&
                            $next[$mainAttribute] == $current[$mainAttribute]
                        ) {
                            $skipNext = true;
                            $data[$i] = [
                                $groupField           => $current[$groupField],
                                'sale_price'          => $current['sale_price'],
                                'sale_price_discount' => $next['sale_price'],
                                'qty'                 => $current['qty'],
                                'qty_discount'        => $next['qty'],
                                'qty_global'          => $next['qty'] + $current['qty'],
                                'sku'                 => $next['sku'],
                                'row_total'           => $current['sale_price'] * $current['qty'],
                                'row_total_discount'  => $next['sale_price'] * $next['qty'],
                                'unit_price'          => $current['unit_price'],
                                'profit'              => $current['profit'],
                                'cost'                => $current['cost'],
                                'taxes'               => $current['taxes'],
                            ];

                            if ($extraField) {
                                $data[$i][$extraField] = $current[$extraField];
                            }
                            if ($nextField) {
                                $data[$i][$nextField] = $current[$nextField];
                            }
                        } else {
                            $data[$i] = [
                                $groupField           => $current[$groupField],
                                'sale_price'          => $current['sale_price'],
                                'sale_price_discount' => null,
                                'qty'                 => $current['qty'],
                                'qty_discount'        => null,
                                'qty_global'          => $current['qty'],
                                'sku'                 => $current['sku'],
                                'row_total'           => $current['sale_price'] * $current['qty'],
                                'row_total_discount'  => null,
                                'unit_price'          => $current['unit_price'],
                                'profit'              => $current['profit'],
                                'cost'                => $current['cost'],
                                'taxes'               => $current['taxes'],
                            ];

                            if ($extraField) {
                                $data[$i][$extraField] = $current[$extraField];
                            }
                            if ($nextField) {
                                $data[$i][$nextField] = $current[$nextField];
                            }
                        }

                        if ($segmentId) {
                            $data[$i]['segment_id'] = $segmentId;
                        }

                        if ($type == 'attribute') {
                            $data[$i]['attribute'] = $current[$attributeCode];
                            $data[$i]['attribute_2'] = $current[$attributeCodeSecondary];
                            $data[$i]['sku'] = $current[$attributeCode];
                            $data[$i]['attribute_code'] = $attributeCode;
                            $data[$i]['attribute_code_2'] = $attributeCodeSecondary;
                        }

                        $data[$i]['day'] = $current['day'];
                        $data[$i]['date'] = $current['date'];
                        $data[$i]['year'] = $current['year'];
                        $data[$i]['month'] = $current['month'];
                        $data[$i]['weekday'] = $current['weekday'];
                        $data[$i]['day_year'] = $current['day_year'];

                        if ($type == 'country') {
                            $data[$i]['country'] = $current['country'];
                        }

                        if ($type == 'region') {
                            $data[$i]['region'] = $current['region'];
                        }

                        if ($type == 'gender') {
                            $data[$i]['gender'] = $current['gender'];
                        }
                        if ($type == 'age') {
                            $data[$i]['age'] = $current['age_one'];
                        }

                        $data[$i]['row_total_global'] = $data[$i]['row_total_discount'] + $data[$i]['row_total'];

                        if ($data[$i]['row_total_discount'] > 0) {
                            $data[$i]['row_total_discount_percentage'] = $data[$i]['row_total_discount'] * 100 /
                                                                         $data[$i]['row_total_global'];
                        }

                        if (isset($history[$current['sku']])) {
                            $data[$i] = array_merge(
                                $data[$i],
                                [
                                    'previous_sale_price'          => $history[$current['sku']]['sale_price'],
                                    'previous_sale_price_discount' => $history[$current['sku']]['sale_price_discount'],
                                    'previous_qty'                 => $history[$current['sku']]['qty'],
                                    'previous_qty_discount'        => $history[$current['sku']]['qty_discount'],
                                    'previous_qty_global'          => $history[$current['sku']]['qty_global'],
                                    'previous_row_total'           => $history[$current['sku']]['row_total'],
                                    'previous_row_total_discount'  => $history[$current['sku']]['row_total_discount'],
                                    'previous_unit_price'          => $current['unit_price'],
                                    'previous_profit'              => $current['profit'],
                                    'previous_cost'                => $current['cost'],
                                    'previous_taxes'               => $current['taxes'],
                                ]
                            );
                        }

                        if (isset($data[$i])) {
                            $history[$current['sku']] = $data[$i];
                        }
                    }

                    if ($date) {
                        $deleteWhere['date=?'] = $date;
                    }

                    if ($segmentId) {
                        $deleteWhere['segment_id=?'] = $segmentId;
                    } else {
                        $deleteWhere['segment_id IS NULL'] = '';
                    }

                    if ($attributeCode) {
                        $deleteWhere['attribute_code=?'] = $attributeCode;
                    }

                    $connection->delete($mainTable, $deleteWhere);

                    foreach ($data as $insert) {
                        $insert = array_intersect_key($insert, $this->describeTable($mainTable));

                        # try {
                        $connection->insert($mainTable, $insert);
                        # } catch (\Exception $e) {
                        #     $this->pandaHelper->logException($e);
                        # }
                    }
                }
            }
        }

        if ($output = $this->getData('consoleOutput')) {
            if ($output instanceof OutputInterface) {
                $extra = '';
                if ($segmentId) {
                    $extra .= ' / SEG ID:' . $segmentId;
                }

                $extra .= ' - ' . $this->pandaHelper->gmtDate('Y-m-d H:i:s');

                $output->writeln("Performance | Finished: " . $type . " / " . $extra);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getGroups()
    {

        return [
            'date'       => '-None-',
            'day'        => 'Day Month',
            'weekday'    => 'Weekday',
            'month'      => 'Month',
            'year'       => 'Year',
            'year_month' => 'Year/Month',
        ];
    }

    /**
     * @return array
     */
    public function getTypes()
    {

        $types = explode(',', $this->scopeConfig->getValue('panda_equity/reports/types'));
        $types = array_flip($types);

        return array_intersect_key(\Licentia\Reports\Model\Source\ReportTypes::PANDA_REPORT_TYPES, $types);
    }

    /**
     * @param $mainTable
     *
     * @return mixed
     */
    public function describeTable($mainTable)
    {

        $connection = $this->statsFactory->getConnection();

        if (!isset($this->describeTable[$mainTable])) {
            $this->describeTable[$mainTable] = $connection->describeTable($mainTable);
        }

        return $this->describeTable[$mainTable];
    }

    /**
     * @param $age
     *
     * @return string
     */
    public function getAgeName($age)
    {

        $connection = $this->statsFactory->getConnection();

        $select = $connection->select()
                             ->from(
                                 $this->statsFactory
                                     ->getTable('eav_attribute_option_value'),
                                 ['value']
                             )
                             ->where('option_id=?', $age)
                             ->where('store_id=?', 0);

        return $connection->fetchOne($select);
    }

    /**
     * @param $attributeCode
     *
     * @return array
     */
    public function getAttributeOptions($attributeCode)
    {

        $resource = $this->statsFactory;

        $select = $resource->getConnection()->select();

        $select->from(['ea' => $resource->getTable('eav_attribute')], [])
               ->join(['eao' => $resource->getTable('eav_attribute_option')], 'eao.attribute_id=ea.attribute_id', [])
               ->join(
                   ['eaov' => $resource->getTable('eav_attribute_option_value')],
                   'eaov.option_id=eao.option_id',
                   ['option_id']
               )
               ->where('ea.attribute_code=?', $attributeCode)
               ->order('eaov.value');

        return $resource->getConnection()->fetchCol($select);
    }

    /**
     * @return array
     */
    public function getDistinctAttributes()
    {

        $mainTable = self::PERFORMANCE_TABLE_PREFIX . 'attribute';

        $connection = $this->statsFactory->getConnection();

        return $connection->fetchCol(
            $connection
                ->select()
                ->from(
                    $this->statsFactory->getTable($mainTable),
                    [new \Zend_Db_Expr('DISTINCT(attribute)')]
                )
        );
    }

    /**
     * @param $type
     *
     * @return bool
     */
    public function shouldWeRebuildForType($type)
    {

        $connection = $this->getResource()->getConnection();

        if ($type == 'age') {
            $totalAges = $connection->fetchCol(
                $connection->select()
                           ->from($this->getResource()->getTable('panda_customers_kpis'), ['age'])
                           ->distinct()
            );

            array_filter($totalAges);

            if (!$totalAges) {
                return false;
            }
        }

        if ($type == 'female' || $type == 'male') {
            $totalGender = $connection->fetchOne(
                $connection->select()
                           ->from($this->getResource()->getTable('panda_customers_kpis'))
                           ->where('gender=?', $type)
                           ->limit(1)
            );

            if (!$totalGender) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $itemId
     *
     * @return $this
     */
    public function setItemId($itemId)
    {

        return $this->setData('item_id', $itemId);
    }

    /**
     * @param $segmentId
     *
     * @return $this
     */
    public function setSegmentId($segmentId)
    {

        return $this->setData('segment_id', $segmentId);
    }

    /**
     * @param $date
     *
     * @return $this
     */
    public function setDate($date)
    {

        return $this->setData('date', $date);
    }

    /**
     * @param $day
     *
     * @return $this
     */
    public function setDay($day)
    {

        return $this->setData('day', $day);
    }

    /**
     * @param $dayYear
     *
     * @return $this
     */
    public function setDayYear($dayYear)
    {

        return $this->setData('day_year', $dayYear);
    }

    /**
     * @param $weekday
     *
     * @return $this
     */
    public function setWeekday($weekday)
    {

        return $this->setData('weekday', $weekday);
    }

    /**
     * @param $year
     *
     * @return $this
     */
    public function setYear($year)
    {

        return $this->setData('year', $year);
    }

    /**
     * @param $month
     *
     * @return $this
     */
    public function setMonth($month)
    {

        return $this->setData('month', $month);
    }

    /**
     * @param $sku
     *
     * @return $this
     */
    public function setSku($sku)
    {

        return $this->setData('sku', $sku);
    }

    /**
     * @param $salePrice
     *
     * @return $this
     */
    public function setSalePrice($salePrice)
    {

        return $this->setData('sale_price', $salePrice);
    }

    /**
     * @param $salePriceDiscount
     *
     * @return $this
     */
    public function setSalePriceDiscount($salePriceDiscount)
    {

        return $this->setData('sale_price_discount', $salePriceDiscount);
    }

    /**
     * @param $qty
     *
     * @return $this
     */
    public function setQty($qty)
    {

        return $this->setData('qty', $qty);
    }

    /**
     * @param $qtyDiscount
     *
     * @return $this
     */
    public function setQtyDiscount($qtyDiscount)
    {

        return $this->setData('qty_discount', $qtyDiscount);
    }

    /**
     * @param $qtyGlobal
     *
     * @return $this
     */
    public function setQtyGlobal($qtyGlobal)
    {

        return $this->setData('qty_global', $qtyGlobal);
    }

    /**
     * @param $rowTotal
     *
     * @return $this
     */
    public function setRowTotal($rowTotal)
    {

        return $this->setData('row_total', $rowTotal);
    }

    /**
     * @param $rowTotalDiscount
     *
     * @return $this
     */
    public function setRowTotalDiscount($rowTotalDiscount)
    {

        return $this->setData('row_total_discount', $rowTotalDiscount);
    }

    /**
     * @param $rowTotalGlobal
     *
     * @return $this
     */
    public function setRowTotalGlobal($rowTotalGlobal)
    {

        return $this->setData('row_total_global', $rowTotalGlobal);
    }

    /**
     * @param $rowTotalDiscountPercentage
     *
     * @return $this
     */
    public function setRowTotalDiscountPercentage($rowTotalDiscountPercentage)
    {

        return $this->setData('row_total_discount_percentage', $rowTotalDiscountPercentage);
    }

    /**
     * @param $unitPrice
     *
     * @return $this
     */
    public function setUnitPrice($unitPrice)
    {

        return $this->setData('unit_price', $unitPrice);
    }

    /**
     * @param $profit
     *
     * @return $this
     */
    public function setProfit($profit)
    {

        return $this->setData('profit', $profit);
    }

    /**
     * @param $cost
     *
     * @return $this
     */
    public function setCost($cost)
    {

        return $this->setData('cost', $cost);
    }

    /**
     * @param $taxes
     *
     * @return $this
     */
    public function setTaxes($taxes)
    {

        return $this->setData('taxes', $taxes);
    }

    /**
     * @param $previousSalePrice
     *
     * @return $this
     */
    public function setPreviousSalePrice($previousSalePrice)
    {

        return $this->setData('previous_sale_price', $previousSalePrice);
    }

    /**
     * @param $previousSalePriceDiscount
     *
     * @return $this
     */
    public function setPreviousSalePriceDiscount($previousSalePriceDiscount)
    {

        return $this->setData('previous_sale_price_discount', $previousSalePriceDiscount);
    }

    /**
     * @param $previousQty
     *
     * @return $this
     */
    public function setPreviousQty($previousQty)
    {

        return $this->setData('previous_qty', $previousQty);
    }

    /**
     * @param $previousQtyDiscount
     *
     * @return $this
     */
    public function setPreviousQtyDiscount($previousQtyDiscount)
    {

        return $this->setData('previous_qty_discount', $previousQtyDiscount);
    }

    /**
     * @param $previousQtyGlobal
     *
     * @return $this
     */
    public function setPreviousQtyGlobal($previousQtyGlobal)
    {

        return $this->setData('previous_qty_global', $previousQtyGlobal);
    }

    /**
     * @param $previousRowTotal
     *
     * @return $this
     */
    public function setPreviousRowTotal($previousRowTotal)
    {

        return $this->setData('previous_row_total', $previousRowTotal);
    }

    /**
     * @param $previousRowTotalDiscount
     *
     * @return $this
     */
    public function setPreviousRowTotalDiscount($previousRowTotalDiscount)
    {

        return $this->setData('previous_row_total_discount', $previousRowTotalDiscount);
    }

    /**
     * @param $previousRowTotalGlobal
     *
     * @return $this
     */
    public function setPreviousRowTotalGlobal($previousRowTotalGlobal)
    {

        return $this->setData('previous_row_total_global', $previousRowTotalGlobal);
    }

    /**
     * @param $previousRowTotalDiscountPercentage
     *
     * @return $this
     */
    public function setPreviousRowTotalDiscountPercentage($previousRowTotalDiscountPercentage)
    {

        return $this->setData('previous_row_total_discount_percentage', $previousRowTotalDiscountPercentage);
    }

    /**
     * @param $previousUnitPrice
     *
     * @return $this
     */
    public function setPreviousUnitPrice($previousUnitPrice)
    {

        return $this->setData('previous_unit_price', $previousUnitPrice);
    }

    /**
     * @param $previousProfit
     *
     * @return $this
     */
    public function setPreviousProfit($previousProfit)
    {

        return $this->setData('previous_profit', $previousProfit);
    }

    /**
     * @param $previousCost
     *
     * @return $this
     */
    public function setPreviousCost($previousCost)
    {

        return $this->setData('previous_cost', $previousCost);
    }

    /**
     * @param $previousTaxes
     *
     * @return $this
     */
    public function setPreviousTaxes($previousTaxes)
    {

        return $this->setData('previous_taxes', $previousTaxes);
    }

    /**
     * @return mixed
     */
    public function getItemId()
    {

        return $this->getData('item_id');
    }

    /**
     * @return mixed
     */
    public function getSegmentId()
    {

        return $this->getData('segment_id');
    }

    /**
     * @return mixed
     */
    public function getDate()
    {

        return $this->getData('date');
    }

    /**
     * @return mixed
     */
    public function getDay()
    {

        return $this->getData('day');
    }

    /**
     * @return mixed
     */
    public function getDayYear()
    {

        return $this->getData('day_year');
    }

    /**
     * @return mixed
     */
    public function getWeekday()
    {

        return $this->getData('weekday');
    }

    /**
     * @return mixed
     */
    public function getYear()
    {

        return $this->getData('year');
    }

    /**
     * @return mixed
     */
    public function getMonth()
    {

        return $this->getData('month');
    }

    /**
     * @return mixed
     */
    public function getSku()
    {

        return $this->getData('sku');
    }

    /**
     * @return mixed
     */
    public function getSalePrice()
    {

        return $this->getData('sale_price');
    }

    /**
     * @return mixed
     */
    public function getSalePriceDiscount()
    {

        return $this->getData('sale_price_discount');
    }

    /**
     * @return mixed
     */
    public function getQty()
    {

        return $this->getData('qty');
    }

    /**
     * @return mixed
     */
    public function getQtyDiscount()
    {

        return $this->getData('qty_discount');
    }

    /**
     * @return mixed
     */
    public function getQtyGlobal()
    {

        return $this->getData('qty_global');
    }

    /**
     * @return mixed
     */
    public function getRowTotal()
    {

        return $this->getData('row_total');
    }

    /**
     * @return mixed
     */
    public function getRowTotalDiscount()
    {

        return $this->getData('row_total_discount');
    }

    /**
     * @return mixed
     */
    public function getRowTotalGlobal()
    {

        return $this->getData('row_total_global');
    }

    /**
     * @return mixed
     */
    public function getRowTotalDiscountPercentage()
    {

        return $this->getData('row_total_discount_percentage');
    }

    /**
     * @return mixed
     */
    public function getUnitPrice()
    {

        return $this->getData('unit_price');
    }

    /**
     * @return mixed
     */
    public function getProfit()
    {

        return $this->getData('profit');
    }

    /**
     * @return mixed
     */
    public function getCost()
    {

        return $this->getData('cost');
    }

    /**
     * @return mixed
     */
    public function getTaxes()
    {

        return $this->getData('taxes');
    }

    /**
     * @return mixed
     */
    public function getPreviousSalePrice()
    {

        return $this->getData('previous_sale_price');
    }

    /**
     * @return mixed
     */
    public function getPreviousSalePriceDiscount()
    {

        return $this->getData('previous_sale_price_discount');
    }

    /**
     * @return mixed
     */
    public function getPreviousQty()
    {

        return $this->getData('previous_qty');
    }

    /**
     * @return mixed
     */
    public function getPreviousQtyDiscount()
    {

        return $this->getData('previous_qty_discount');
    }

    /**
     * @return mixed
     */
    public function getPreviousQtyGlobal()
    {

        return $this->getData('previous_qty_global');
    }

    /**
     * @return mixed
     */
    public function getPreviousRowTotal()
    {

        return $this->getData('previous_row_total');
    }

    /**
     * @return mixed
     */
    public function getPreviousRowTotalDiscount()
    {

        return $this->getData('previous_row_total_discount');
    }

    /**
     * @return mixed
     */
    public function getPreviousRowTotalGlobal()
    {

        return $this->getData('previous_row_total_global');
    }

    /**
     * @return mixed
     */
    public function getPreviousRowTotalDiscountPercentage()
    {

        return $this->getData('previous_row_total_discount_percentage');
    }

    /**
     * @return mixed
     */
    public function getPreviousUnitPrice()
    {

        return $this->getData('previous_unit_price');
    }

    /**
     * @return mixed
     */
    public function getPreviousProfit()
    {

        return $this->getData('previous_profit');
    }

    /**
     * @return mixed
     */
    public function getPreviousCost()
    {

        return $this->getData('previous_cost');
    }

    /**
     * @return mixed
     */
    public function getPreviousTaxes()
    {

        return $this->getData('previous_taxes');
    }
}
