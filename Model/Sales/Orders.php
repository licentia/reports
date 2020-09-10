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

use \Licentia\Reports\Model\Indexer;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Orders
 *
 * @package Licentia\Panda\Model\Sales
 */
class Orders extends \Magento\Framework\Model\AbstractModel
{

    /**
     *
     */
    const FIELDS_SQL_EXP = [
        'sale_price'                    => 'AVG(sale_price)',
        'sale_price_discount'           => 'AVG(sale_price_discount)',
        'row_total'                     => 'SUM(row_total)',
        'row_total_discount'            => 'SUM(row_total_discount)',
        'row_total_global'              => 'SUM(row_total_global)',
        'row_total_discount_percentage' => 'SUM(row_total_discount_percentage)',
        'unit_price'                    => 'AVG(unit_price)',
        'profit'                        => 'SUM(profit)',
        'cost'                          => 'SUM(cost)',
        'taxes'                         => 'SUM(taxes)',
        'qty'                           => 'SUM(qty)',
        'qty_discount'                  => 'SUM(qty_discount)',
        'qty_global'                    => 'SUM(qty_global)',
    ];

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'panda_sales_orders';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'panda_sales_orders';

    /**
     * @var \Licentia\Equity\Model\SegmentsFactory
     */
    protected $segmentsFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var
     */
    protected $describeTable;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Licentia\Reports\Model\IndexerFactory
     */
    protected $indexer;

    /**
     * @var StatsFactory
     */
    protected $salesStats;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected $pandaHelper;

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
     * Orders constructor.
     *
     * @param \Licentia\Reports\Helper\Data                                $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface           $scopeConfigInterface
     * @param StatsFactory                                                 $statsFactory
     * @param \Licentia\Reports\Model\IndexerFactory                       $indexer
     * @param \Magento\Framework\Filesystem                                $filesystem
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface         $timezone
     * @param \Licentia\Equity\Model\SegmentsFactory                       $segmentsFactory
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Licentia\Reports\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        StatsFactory $statsFactory,
        \Licentia\Reports\Model\IndexerFactory $indexer,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Licentia\Equity\Model\SegmentsFactory $segmentsFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->filesystem = $filesystem;
        $this->segmentsFactory = $segmentsFactory;
        $this->timezone = $timezone;
        $this->indexer = $indexer->create();
        $this->salesStats = $statsFactory;
        $this->scopeConfig = $scopeConfigInterface;
        $this->pandaHelper = $helper;
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
     * @return array
     */
    public function getPeriodsInCollection($skus, $type = 'global', $group = 'date')
    {

        $resource = $this->getResource();
        $connection = $resource->getConnection();

        $tableName = $resource->getTable('panda_sales_stats_' . $type);

        $order = $group;

        $days = $connection->fetchCol(
            $connection->select()
                       ->from($tableName, [])
                       ->columns(
                           [
                               'distinct' => new \Zend_Db_Expr("DISTINCT($order)"),
                           ]
                       )
                       ->where('sku IN (?)', $skus)
                       ->order($order)
        );

        return $days;
    }

    /**
     * @param        $skus
     * @param string $type
     * @param string $field
     *
     * @return array
     */
    public function getAgesInCollection($skus, $type = 'global', $field = 'age')
    {

        $resource = $this->getResource();
        $connection = $resource->getConnection();

        $tableName = $resource->getTable('panda_sales_stats_' . $type);

        $days = $connection->fetchCol(
            $connection->select()
                       ->from($tableName, [])
                       ->columns(
                           [
                               'distinct' => new \Zend_Db_Expr("DISTINCT($field)"),
                           ]
                       )
                       ->where('sku IN (?)', $skus)
                       ->order($field)
        );

        array_unshift($days, 0);

        return $days;
    }

    /**
     * @param        $skus
     * @param string $type
     * @param string $group
     * @param null   $segmentId
     *
     * @return array
     */
    public function getStatsCollection($skus, $type = 'global', $group = 'date', $segmentId = null)
    {

        $collection = [];

        $resource = $this->getResource();
        $connection = $resource->getConnection();

        $tableName = $resource->getTable('panda_sales_stats_' . $type);

        $order = $group;
        $days = $this->getPeriodsInCollection($skus, $type, $group);

        $auxFields = ['age', 'country', 'region', 'gender'];

        if (in_array($type, $auxFields)) {
            $ages = $this->getAgesInCollection($skus, $type, $type);
        } else {
            $ages = [0];
        }

        foreach ($skus as $sku) {
            foreach ($ages as $age) {
                $select = $connection->select()
                                     ->from($tableName)
                                     ->where('sku=?', (string) $sku)
                                     ->order($order);

                if (in_array($type, $auxFields) && $age !== 0) {
                    $fieldSelect = $type;

                    $select->where($fieldSelect . '=?', $age);
                }

                if ($segmentId) {
                    $select->where('segment_id=?', $segmentId);
                }

                if (in_array($type, $auxFields) && $age === 0) {
                    $select = $connection->select()
                                         ->from($tableName)
                                         ->columns(self::FIELDS_SQL_EXP)
                                         ->where('sku=?', (string) $sku)
                                         ->group($order)
                                         ->order($order);

                    if ($segmentId) {
                        $select->where('segment_id=?', $segmentId);
                    }
                }
                $collection[$sku][$age] = $connection->fetchAll($select);
            }

            foreach ($ages as $age) {
                foreach ($collection[$sku][$age] as $key => $value) {
                    $collection[$sku][$value[$order]][$age] = $value;
                    unset($collection[$sku][$age]);
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
                if (array_key_exists($age, $collection[$sku])) {
                    unset($collection[$sku][$age]);
                }
            }
        }

        return $collection;
    }

    /**
     * @return Orders
     */
    public function reindexSales()
    {

        return $this->rebuildAll();
    }

    /**
     * @param null $date
     *
     * @return $this
     */
    public function rebuildAll($date = null)
    {

        if (!$this->getData('consoleOutput') && !$this->indexer->canReindex('sales')) {
            throw new \RuntimeException("Indexer status does not allow reindexing");
        }

        $types = $this->getTypes();

        $segments = $this->segmentsFactory->create()
                                          ->getCollection()
                                          ->addFieldToFilter('products_relations', 1);

        $types = $this->indexer->getTypesToReindex($types, 'sales');
        $this->indexer->updateIndexStatus(Indexer::STATUS_WORKING, 'sales');

        foreach ($types as $keyType => $type) {
            $this->rebuildItem($keyType, null, $date);

            /** @var \Licentia\Equity\Model\Segments $segment */
            foreach ($segments as $segment) {
                $this->rebuildItem($keyType, $segment->getId(), $date);
            }

            $this->indexer->updateIndex($type, 0, 'sales');
        }

        $this->indexer->updateIndex('sales', 1, 'sales');
        $this->indexer->updateIndexStatus(Indexer::STATUS_VALID, 'sales');

        return $this;
    }

    /**
     * @return Orders
     */
    public function rebuildForYesterday()
    {

        $date = $this->timezone->date($this->pandaHelper->gmtDate())
                               ->sub(new \DateInterval('P1D'))
                               ->format('Y-m-d');

        return $this->rebuildAll($date);
    }

    /**
     * @param string $type
     * @param null   $segmentId
     * @param null   $date
     *
     * @return $this
     * @throws \Exception
     */
    public function rebuildItem($type = 'global', $segmentId = null, $date = null)
    {

        $type = strtolower($type);

        if (!array_key_exists($type, $this->getTypes())) {
            throw new \Exception('Invalid Type:' . $type);
        }

        $resource = $this->getResource();
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

        $pandaCustomerKpisTable = $resource->getTable('panda_customers_kpis');
        $pandaSegmentsRecordsTable = $resource->getTable('panda_segments_records');
        $pandaSalesAddressTable = $resource->getTable('sales_order_address');

        // Columns list
        $selectColumns = [
            'orders_count'                 => new \Zend_Db_Expr('COUNT(o.entity_id)'),
            'total_qty_ordered'            => new \Zend_Db_Expr('SUM(oi.total_qty_ordered)'),
            'total_qty_invoiced'           => new \Zend_Db_Expr('SUM(oi.total_qty_invoiced)'),
            'total_income_amount'          => new \Zend_Db_Expr(
                sprintf(
                    'SUM((%s - %s) * %s)',
                    $connection->getIfNullSql('o.base_grand_total', 0),
                    $connection->getIfNullSql('o.base_total_canceled', 0),
                    $connection->getIfNullSql('o.base_to_global_rate', 0)
                )
            ),
            'total_revenue_amount'         => new \Zend_Db_Expr(
                sprintf(
                    'SUM((%s - %s - %s - (%s - %s - %s)) * %s)',
                    $connection->getIfNullSql('o.base_total_invoiced', 0),
                    $connection->getIfNullSql('o.base_tax_invoiced', 0),
                    $connection->getIfNullSql('o.base_shipping_invoiced', 0),
                    $connection->getIfNullSql('o.base_total_refunded', 0),
                    $connection->getIfNullSql('o.base_tax_refunded', 0),
                    $connection->getIfNullSql('o.base_shipping_refunded', 0),
                    $connection->getIfNullSql('o.base_to_global_rate', 0)
                )
            ),
            'total_profit_amount'          => new \Zend_Db_Expr(
                sprintf(
                    'SUM((%s - %s - %s - %s - %s) * %s)',
                    $connection->getIfNullSql('o.base_total_paid', 0),
                    $connection->getIfNullSql('o.base_total_refunded', 0),
                    $connection->getIfNullSql('o.base_tax_invoiced', 0),
                    $connection->getIfNullSql('o.base_shipping_invoiced', 0),
                    $connection->getIfNullSql('o.base_total_invoiced_cost', 0),
                    $connection->getIfNullSql('o.base_to_global_rate', 0)
                )
            ),
            'total_invoiced_amount'        => new \Zend_Db_Expr(
                sprintf(
                    'SUM(%s * %s)',
                    $connection->getIfNullSql('o.base_total_invoiced', 0),
                    $connection->getIfNullSql('o.base_to_global_rate', 0)
                )
            ),
            'total_canceled_amount'        => new \Zend_Db_Expr(
                sprintf(
                    'SUM(%s * %s)',
                    $connection->getIfNullSql('o.base_total_canceled', 0),
                    $connection->getIfNullSql('o.base_to_global_rate', 0)
                )
            ),
            'total_paid_amount'            => new \Zend_Db_Expr(
                sprintf(
                    'SUM(%s * %s)',
                    $connection->getIfNullSql('o.base_total_paid', 0),
                    $connection->getIfNullSql('o.base_to_global_rate', 0)
                )
            ),
            'total_refunded_amount'        => new \Zend_Db_Expr(
                sprintf(
                    'SUM(%s * %s)',
                    $connection->getIfNullSql('o.base_total_refunded', 0),
                    $connection->getIfNullSql('o.base_to_global_rate', 0)
                )
            ),
            'total_tax_amount'             => new \Zend_Db_Expr(
                sprintf(
                    'SUM((%s - %s) * %s)',
                    $connection->getIfNullSql('o.base_tax_amount', 0),
                    $connection->getIfNullSql('o.base_tax_canceled', 0),
                    $connection->getIfNullSql('o.base_to_global_rate', 0)
                )
            ),
            'total_tax_amount_actual'      => new \Zend_Db_Expr(
                sprintf(
                    'SUM((%s -%s) * %s)',
                    $connection->getIfNullSql('o.base_tax_invoiced', 0),
                    $connection->getIfNullSql('o.base_tax_refunded', 0),
                    $connection->getIfNullSql('o.base_to_global_rate', 0)
                )
            ),
            'total_shipping_amount'        => new \Zend_Db_Expr(
                sprintf(
                    'SUM((%s - %s) * %s)',
                    $connection->getIfNullSql('o.base_shipping_amount', 0),
                    $connection->getIfNullSql('o.base_shipping_canceled', 0),
                    $connection->getIfNullSql('o.base_to_global_rate', 0)
                )
            ),
            'total_shipping_amount_actual' => new \Zend_Db_Expr(
                sprintf(
                    'SUM((%s - %s) * %s)',
                    $connection->getIfNullSql('o.base_shipping_invoiced', 0),
                    $connection->getIfNullSql('o.base_shipping_refunded', 0),
                    $connection->getIfNullSql('o.base_to_global_rate', 0)
                )
            ),
            'total_discount_amount'        => new \Zend_Db_Expr(
                sprintf(
                    'SUM((ABS(%s) - %s) * %s)',
                    $connection->getIfNullSql('o.base_discount_amount', 0),
                    $connection->getIfNullSql('o.base_discount_canceled', 0),
                    $connection->getIfNullSql('o.base_to_global_rate', 0)
                )
            ),
            'total_discount_amount_actual' => new \Zend_Db_Expr(
                sprintf(
                    'SUM((%s - %s) * %s)',
                    $connection->getIfNullSql('o.base_discount_invoiced', 0),
                    $connection->getIfNullSql('o.base_discount_refunded', 0),
                    $connection->getIfNullSql('o.base_to_global_rate', 0)
                )
            ),
        ];

        $select = $connection->select();
        $selectOrderItem = $connection->select();

        $qtyCanceledExpr = $connection->getIfNullSql('qty_canceled', 0);
        $cols = [
            'order_id'           => 'order_id',
            'total_qty_ordered'  => new \Zend_Db_Expr("SUM(qty_ordered - {$qtyCanceledExpr})"),
            'total_qty_invoiced' => new \Zend_Db_Expr('SUM(qty_invoiced)'),
        ];
        $selectOrderItem->from($resource->getTable('sales_order_item'), $cols)
                        ->where('parent_item_id IS NULL')
                        ->group('order_id');

        $selectColumns['month'] = new \Zend_Db_Expr("MONTH(o.created_at)");
        $selectColumns['year'] = new \Zend_Db_Expr("YEAR(o.created_at)");
        $selectColumns['date'] = new \Zend_Db_Expr("date_format(o.created_at,'%Y-%m-%d')");
        $selectColumns['day'] = new \Zend_Db_Expr("date_format(o.created_at,'%d')");
        $selectColumns['weekday'] = new \Zend_Db_Expr("date_format(o.created_at,'%w')");
        $selectColumns['day_year'] = new \Zend_Db_Expr("date_format(o.created_at,'%j')");

        $mainTable = 'panda_sales_stats_' . $type;

        $mainTable = $resource->getTable($mainTable);

        if ($type == 'country') {
            $selectColumns['country'] = new \Zend_Db_Expr("addr.country_id");

            $countries = array_filter(
                explode(
                    ',',
                    $this->scopeConfig->getValue(
                        'panda_equity/reports/country',
                        ScopeInterface::SCOPE_WEBSITE
                    )
                )
            );

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
            $selectColumns['age_one'] = new \Zend_Db_Expr(\Licentia\Reports\Helper\Data::getAgeMySQLGroup($this->getMySQLVersion()));
        }

        if ($type == 'gender') {
            $selectColumns['gender'] = "kpi.gender";
        }

        $select->join(
            ['kpi' => $pandaCustomerKpisTable],
            "kpi.email_meta = o.customer_email",
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
            $select->where('kpi.age >=18');
        } elseif ($type == 'gender') {
            $select->where('LENGTH(kpi.gender)>3');
        }

        if ($type == 'country' || $type == 'region') {
            $select->join(
                ['addr' => $pandaSalesAddressTable],
                "addr.parent_id=o.entity_id AND addr.address_type='billing'",
                []
            );
        }

        if ($segmentId) {
            $select->where("$pandaSegmentsRecordsTable.segment_id =?", $segmentId);
        }

        if ($date) {
            $select->where("DATE_FORMAT(o.created_at,'%Y-%m-%d')=?", $date);
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

        if ($type == 'gender') {
            $select->group("kpi.gender");
        }

        $select->group('date');
        $select->order('date');

        $select->from(['o' => $resource->getTable('sales_order')], $selectColumns)
               ->join(['oi' => $selectOrderItem], 'oi.order_id = o.entity_id', [])
               ->where(
                   'o.state NOT IN (?)',
                   [\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, \Magento\Sales\Model\Order::STATE_NEW]
               );

        $result = $connection->fetchAll($select);
        $totalResults = count($result);

        $data = [];
        for ($i = 0; $i < $totalResults; $i++) {
            $current = $result[$i];

            $data[$i] = $current;

            if ($extraField) {
                $data[$i][$extraField] = $current[$extraField];
            }

            if ($nextField) {
                $data[$i][$nextField] = $current[$nextField];
            }

            if ($segmentId) {
                $data[$i]['segment_id'] = $segmentId;
            }

            if ($type == 'country') {
                $data[$i]['country'] = $current['country'];
            }

            if ($type == 'region') {
                $data[$i]['region'] = $current['region'];
                $data[$i]['country'] = $current['country'];
            }

            if ($type == 'gender') {
                $data[$i]['gender'] = $current['gender'];
            }

            if ($type == 'age') {
                $data[$i]['age'] = $current['age_one'];
            }
        }

        $deleteWhere = [];

        if ($date) {
            $deleteWhere['date=?'] = $date;
        }
        if ($segmentId) {
            $deleteWhere['segment_id=?'] = $segmentId;
        } else {
            $deleteWhere['segment_id IS NULL'] = '';
        }

        $connection->delete($mainTable, $deleteWhere);

        foreach ($data as $insert) {
            $insert = array_intersect_key($insert, $this->describeTable($mainTable));

            try {
                $connection->insert($mainTable, $insert);
            } catch (\Exception $e) {
                $this->pandaHelper->logException($e);
            }
        }

        if ($output = $this->getData('consoleOutput')) {
            if ($output instanceof \Symfony\Component\Console\Output\OutputInterface) {
                $extra = '';
                if ($segmentId) {
                    $extra = ' / SEGID: ' . $segmentId;
                }

                $extra .= ' - ' . $this->pandaHelper->gmtDate('Y-m-d H:i:s');

                $output->writeln("SalesOrders | Finished: " . $mainTable . $extra);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public static function getGroups()
    {

        return [
            'date'    => 'Day',
            'day'     => 'Day of Month',
            'weekday' => 'Week Day',
            'month'   => 'Month',
            'year'    => 'Year',
        ];
    }

    /**
     * @return array
     */
    public function getTypes()
    {

        $types = array_keys($this->salesStats->create()->getTypes());

        $types = array_combine($types, $types);
        unset($types['attribute']);

        $types = array_combine($types, $types);

        return $types;
    }

    /**
     * @param $mainTable
     *
     * @return mixed
     */
    public function describeTable($mainTable)
    {

        $connection = $this->getResource()->getConnection();

        if (!isset($this->describeTable[$mainTable])) {
            $this->describeTable[$mainTable] = $connection->describeTable($mainTable);
        }

        return $this->describeTable[$mainTable];
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
     * @param $ordersCount
     *
     * @return $this
     */
    public function setOrdersCount($ordersCount)
    {

        return $this->setData('orders_count', $ordersCount);
    }

    /**
     * @param $totalQtyOrdered
     *
     * @return $this
     */
    public function setTotalQtyOrdered($totalQtyOrdered)
    {

        return $this->setData('total_qty_ordered', $totalQtyOrdered);
    }

    /**
     * @param $totalQtyInvoiced
     *
     * @return $this
     */
    public function setTotalQtyInvoiced($totalQtyInvoiced)
    {

        return $this->setData('total_qty_invoiced', $totalQtyInvoiced);
    }

    /**
     * @param $totalIncomeAmount
     *
     * @return $this
     */
    public function setTotalIncomeAmount($totalIncomeAmount)
    {

        return $this->setData('total_income_amount', $totalIncomeAmount);
    }

    /**
     * @param $totalRevenueAmount
     *
     * @return $this
     */
    public function setTotalRevenueAmount($totalRevenueAmount)
    {

        return $this->setData('total_revenue_amount', $totalRevenueAmount);
    }

    /**
     * @param $totalProfitAmount
     *
     * @return $this
     */
    public function setTotalProfitAmount($totalProfitAmount)
    {

        return $this->setData('total_profit_amount', $totalProfitAmount);
    }

    /**
     * @param $totalInvoicedAmount
     *
     * @return $this
     */
    public function setTotalInvoicedAmount($totalInvoicedAmount)
    {

        return $this->setData('total_invoiced_amount', $totalInvoicedAmount);
    }

    /**
     * @param $totalCanceledAmount
     *
     * @return $this
     */
    public function setTotalCanceledAmount($totalCanceledAmount)
    {

        return $this->setData('total_canceled_amount', $totalCanceledAmount);
    }

    /**
     * @param $totalPaidAmount
     *
     * @return $this
     */
    public function setTotalPaidAmount($totalPaidAmount)
    {

        return $this->setData('total_paid_amount', $totalPaidAmount);
    }

    /**
     * @param $totalRefundedAmount
     *
     * @return $this
     */
    public function setTotalRefundedAmount($totalRefundedAmount)
    {

        return $this->setData('total_refunded_amount', $totalRefundedAmount);
    }

    /**
     * @param $totalTaxAmount
     *
     * @return $this
     */
    public function setTotalTaxAmount($totalTaxAmount)
    {

        return $this->setData('total_tax_amount', $totalTaxAmount);
    }

    /**
     * @param $totalTaxAmountActual
     *
     * @return $this
     */
    public function setTotalTaxAmountActual($totalTaxAmountActual)
    {

        return $this->setData('total_tax_amount_actual', $totalTaxAmountActual);
    }

    /**
     * @param $totalShippingAmount
     *
     * @return $this
     */
    public function setTotalShippingAmount($totalShippingAmount)
    {

        return $this->setData('total_shipping_amount', $totalShippingAmount);
    }

    /**
     * @param $totalShippingAmountActual
     *
     * @return $this
     */
    public function setTotalShippingAmountActual($totalShippingAmountActual)
    {

        return $this->setData('total_shipping_amount_actual', $totalShippingAmountActual);
    }

    /**
     * @param $totalDiscountAmount
     *
     * @return $this
     */
    public function setTotalDiscountAmount($totalDiscountAmount)
    {

        return $this->setData('total_discount_amount', $totalDiscountAmount);
    }

    /**
     * @param $totalDiscountAmountActual
     *
     * @return $this
     */
    public function setTotalDiscountAmountActual($totalDiscountAmountActual)
    {

        return $this->setData('total_discount_amount_actual', $totalDiscountAmountActual);
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
    public function getOrdersCount()
    {

        return $this->getData('orders_count');
    }

    /**
     * @return mixed
     */
    public function getTotalQtyOrdered()
    {

        return $this->getData('total_qty_ordered');
    }

    /**
     * @return mixed
     */
    public function getTotalQtyInvoiced()
    {

        return $this->getData('total_qty_invoiced');
    }

    /**
     * @return mixed
     */
    public function getTotalIncomeAmount()
    {

        return $this->getData('total_income_amount');
    }

    /**
     * @return mixed
     */
    public function getTotalRevenueAmount()
    {

        return $this->getData('total_revenue_amount');
    }

    /**
     * @return mixed
     */
    public function getTotalProfitAmount()
    {

        return $this->getData('total_profit_amount');
    }

    /**
     * @return mixed
     */
    public function getTotalInvoicedAmount()
    {

        return $this->getData('total_invoiced_amount');
    }

    /**
     * @return mixed
     */
    public function getTotalCanceledAmount()
    {

        return $this->getData('total_canceled_amount');
    }

    /**
     * @return mixed
     */
    public function getTotalPaidAmount()
    {

        return $this->getData('total_paid_amount');
    }

    /**
     * @return mixed
     */
    public function getTotalRefundedAmount()
    {

        return $this->getData('total_refunded_amount');
    }

    /**
     * @return mixed
     */
    public function getTotalTaxAmount()
    {

        return $this->getData('total_tax_amount');
    }

    /**
     * @return mixed
     */
    public function getTotalTaxAmountActual()
    {

        return $this->getData('total_tax_amount_actual');
    }

    /**
     * @return mixed
     */
    public function getTotalShippingAmount()
    {

        return $this->getData('total_shipping_amount');
    }

    /**
     * @return mixed
     */
    public function getTotalShippingAmountActual()
    {

        return $this->getData('total_shipping_amount_actual');
    }

    /**
     * @return mixed
     */
    public function getTotalDiscountAmount()
    {

        return $this->getData('total_discount_amount');
    }

    /**
     * @return mixed
     */
    public function getTotalDiscountAmountActual()
    {

        return $this->getData('total_discount_amount_actual');
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
