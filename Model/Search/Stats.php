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

namespace Licentia\Reports\Model\Search;

use \Symfony\Component\Console\Output\OutputInterface;
use \Licentia\Reports\Model\Indexer;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Stats
 *
 * @package Licentia\Panda\Model\Sales
 */
class Stats extends \Magento\Framework\Model\AbstractModel
{

    const INDEXER_NAME = 'search_performance';

    /**
     *
     */
    const REPORT_TYPES = [
        'global'  => 'global',
        'male'    => 'male',
        'female'  => 'female',
        'age'     => 'age',
        'country' => 'country',
        'region'  => 'region',
    ];

    /**
     *
     */
    const POSSIBLE_AGE_RANGES = ['18-24', '25-34', '35-44', '45-54', '55-64', '65+',];

    /**
     *
     */
    const SKU_SEPARATOR = '::SEPARATOR::';

    /**
     * @var \Licentia\Equity\Model\ResourceModel\Segments\CollectionFactory
     */
    protected $segmentsCollectionFactory;

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected $pandaHelper;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var
     */
    protected $describeTable;

    /**
     * @var \Licentia\Reports\Model\IndexerFactory
     */
    protected $indexer;

    /**
     * @var \Licentia\Reports\Model\Sales\StatsFactory
     */
    protected $salesStats;

    /**
     * @var mixed
     */
    protected $minSearchNumber;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_init(\Licentia\Reports\Model\ResourceModel\Search\Stats::class);
    }

    /**
     * Stats constructor.
     *
     * @param \Licentia\Reports\Model\Sales\StatsFactory                      $statsFactory
     * @param \Licentia\Reports\Model\IndexerFactory                          $indexer
     * @param \Magento\Framework\App\Config\ScopeConfigInterface              $scopeConfigInterface
     * @param \Licentia\Reports\Helper\Data                                   $pandaHelper
     * @param \Licentia\Equity\Model\ResourceModel\Segments\CollectionFactory $segmentsCollectionFactory
     * @param \Magento\Framework\Model\Context                                $context
     * @param \Magento\Framework\Registry                                     $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null    $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null              $resourceCollection
     * @param array                                                           $data
     */
    public function __construct(
        \Licentia\Reports\Model\Sales\StatsFactory $statsFactory,
        \Licentia\Reports\Model\IndexerFactory $indexer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Licentia\Reports\Helper\Data $pandaHelper,
        \Licentia\Equity\Model\ResourceModel\Segments\CollectionFactory $segmentsCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->indexer = $indexer->create();
        $this->segmentsCollectionFactory = $segmentsCollectionFactory;
        $this->pandaHelper = $pandaHelper;
        $this->scopeConfig = $scopeConfigInterface;
        $this->connection = $segmentsCollectionFactory->create()->getResource()->getConnection();
        $this->salesStats = $statsFactory;

        $this->pandaHelper->getConnection()
                          ->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        $this->minSearchNumber = $this->scopeConfig->getValue('panda_equity/reports/search');
    }

    /**
     * @return string
     */
    public function getMySQLVersion()
    {

        return $this->pandaHelper->getConnection()->fetchOne('SELECT version()');
    }

    /**
     * @param        $sku
     *
     * @return array
     */
    public function getStatsCollection($sku)
    {

        $collection = [];

        $auxFields = ['age', 'country', 'region'];
        $types = self::REPORT_TYPES;

        foreach ($types as $type) {
            if (in_array($type, $auxFields)) {
                $ages = $this->getFieldRange($sku, $type);
            } else {
                $ages = [0];
            }

            $order = '';
            if ($type == 'region') {
                $order = 'region';
            } elseif ($type == 'country') {
                $order = 'country';
            } elseif ($type == 'age') {
                $order = 'age';
            }

            $tableName = $this->getTable('panda_search_relations_' . $type);

            foreach ($ages as $age) {
                $select = $this->connection->select()
                                           ->from($tableName)
                                           ->where('query=?', (string) $sku)
                                           ->where('segment_id IS NULL')
                                           ->order($order);

                if (in_array($type, $auxFields) && $age !== 0) {
                    $select->where($type . '=?', $age);
                }
                try {
                    $collection[$type][$sku][$age] = $this->connection->fetchRow($select);
                } catch (\Exception $e) {
                }
            }
        }

        return $collection;
    }

    /**
     * @param string $type
     * @param string $field
     * @param null   $segment
     * @param string $intervalStart
     * @param string $intervalEnd
     *
     * @return array
     */
    public function getTagCloud($type = 'global', $field = '', $segment = null, $intervalStart = '', $intervalEnd = '')
    {

        $tableName = $this->getTable('panda_search_performance_' . $type);

        $select = $this->connection->select()
                                   ->from($tableName, ['text' => 'query', 'weight' => new \Zend_Db_Expr('SUM(total)')])
                                   ->group('query')
                                   ->order('query')
                                   ->limit(2000);

        if ($field) {
            $select->where($type . '=?', $field);
        }
        if ($segment) {
            $select->where('segment_id=?', $segment);
        } else {
            $select->where('segment_id IS NULL');
        }

        if ($intervalStart) {
            $select->where('date>=?', $intervalStart);
        }

        if ($intervalEnd) {
            $select->where('date<=?', $intervalEnd);
        }

        try {
            $collection = $this->connection->fetchAll($select);
        } catch (\Exception $e) {
        }

        return $collection;
    }

    /**
     * @param $country
     *
     * @return string
     */
    public function countryExists($country)
    {

        return $this->connection->fetchOne(
            $this->connection->select()
                             ->from($this->getTable('panda_search_relations_country'), ['country'])
                             ->where('country=?', $country)
        );
    }

    /**
     * @param $region
     *
     * @return string
     */
    public function regionExists($region)
    {

        return $this->connection->fetchOne(
            $this->connection->select()
                             ->from($this->getTable('panda_search_relations_region'), ['region'])
                             ->where('region=?', $region)
        );
    }

    /**
     * @param        $skus
     * @param string $type
     * @param null   $segmentId
     * @param null   $filter
     *
     * @return array
     */
    public function getVennData($skus, $type = 'global', $segmentId = null, $filter = null)
    {

        if (!$type) {
            $type = 'global';
        }

        $vennHistoryTable = $this->getTable('panda_products_venn_history');

        $row = [];
        if (count($skus) == 1) {
            $tableName = $this->getTable('panda_search_relations_' . $type);

            $sku = reset($skus);
            $row[$sku] = $this->connection->fetchRow(
                $this->connection->select()
                                 ->from($tableName)
                                 ->where('query=?', (string) $sku)
                                 ->where('segment_id IS NULL')
            );

            if ($row[$sku]) {
                for ($i = 1; $i <= 4; $i++) {
                    if ($row[$sku]['related_' . $i]) {
                        $select = $this->connection->select()
                                                   ->from($tableName)
                                                   ->where('query=?', (string) $row[$sku]['related_' . $i])
                                                   ->where('segment_id IS NULL');

                        $row[$row[$sku]['related_' . $i]] = $this->connection->fetchRow($select);
                    }
                }
            }
        } else {
            $row = $skus;
        }

        $final = array_keys($row);
        asort($final);

        $identifier = sha1(implode(self::SKU_SEPARATOR, $final) . $type . $segmentId . json_encode($filter));

        $fields = [];

        foreach (range(1, 50) as $number) {
            $fields[] = 'related_' . $number;
        }

        $startMySQL = $this->pandaHelper->gmtDate();

        $exists = $this->connection->fetchRow(
            $this->connection->select()
                             ->from($vennHistoryTable, ['data', 'item_id'])
                             ->where('identifier=?', $identifier)
                             ->where(
                                 'updated_at >= ? - INTERVAL 1 DAY ',
                                 $this->pandaHelper->gmtDate()
                             )
        );

        if ($exists) {
            $this->connection->update(
                $vennHistoryTable,
                ['views' => new \Zend_Db_Expr('views + 1 ')],
                ['item_id=?' => $exists['item_id']]
            );

            return json_decode($exists['data'], true);
        }

        $collect = $this->createSkuCombination($final);

        $values = [];

        $auxFields = ['country', 'region'];
        $types = self::REPORT_TYPES;
        unset($types['region'], $types['country']);

        if (in_array($type, ['country', 'region'])) {
            $types = [$type];
        }
        #$types = ['age'];
        foreach ($types as $sType) {
            $tableVennName = $this->getTable('panda_search_relations_' . $sType);

            if (in_array($sType, $auxFields) && !$filter) {
                $subTypes = $this->getFieldRangeVenn($sType, $sType);
            } elseif ($sType == 'age') {
                $subTypes = self::POSSIBLE_AGE_RANGES;
            } else {
                $subTypes = [0];
            }

            $order = '';
            if ($sType == 'region') {
                $order = 'region';
            } elseif ($sType == 'country') {
                $order = 'country';
            } elseif ($sType == 'age') {
                $order = 'age';
            }
            foreach ($collect as $list) {
                foreach ($subTypes as $age) {
                    $select = $this->connection->select()
                                               ->from($tableVennName, ['COUNT(*)'])
                                               ->order($order);

                    $tempFields = implode(',', $fields);

                    foreach ($list as $tmpSku) {
                        $select->where("? IN ($tempFields)", $tmpSku);
                    }

                    if (in_array($sType, $auxFields)) {
                        $select->where($sType . '=?', $age);
                    }

                    if ($sType == 'age') {
                        $select->where($sType . '=?', $age);
                    }

                    if ($segmentId) {
                        $select->where('segment_id =?', $segmentId);
                    }

                    if ($filter) {
                        foreach ($filter as $key => $value) {
                            $select->where($key, $value);
                        }
                    }

                    try {
                        $total = $this->connection->fetchOne($select);
                    } catch (\Exception $e) {
                    }

                    if ($total == 0) {
                        continue;
                    }

                    $values[$sType][$age][implode(self::SKU_SEPARATOR, $list)] = $total;
                }
            }
        }

        $execution = $this->connection->fetchOne(
            "SELECT TIMESTAMPDIFF(SECOND,'$startMySQL',?)",
            $this->pandaHelper->gmtDate()
        );

        $result = ['data' => $values, 'skus' => $final, 'execution' => $execution];

        $this->connection->insert(
            $vennHistoryTable,
            [
                'identifier' => $identifier,
                'data'       => json_encode($result),
                'updated_at' => $this->pandaHelper->gmtDate(),
                'execution'  => $execution,
                'views'      => 1,
            ]
        );

        return $result;
    }

    /**
     *
     */
    public function getTableFields()
    {
    }

    /**
     * @param string $type
     * @param bool   $segmentId
     * @param array  $queries
     *
     * @return array
     */
    public function getPossibleVennOptions($type = 'country', $segmentId = false, $queries = [])
    {

        $table = $this->getTable('panda_search_relations_' . $type);

        $select = $this->connection->select()
                                   ->from(
                                       $table,
                                       ["DISTINCT({$this->connection->quoteIdentifier($type)})"]
                                   );
        $fields = [];
        if ($queries) {
            foreach (range(1, 50) as $number) {
                $fields[] = 'related_' . $number;
            }
            $tempFields = implode(',', $fields);
            foreach ($queries as $query) {
                $select->where("? IN ($tempFields)", $query);
            }
        }

        if ($segmentId) {
            $select->where('segment_id=?', $segmentId);
        }

        $select->order($type);

        return $this->connection->fetchCol($select);
    }

    /**
     * @param $array
     *
     * @return array
     */
    public function createSkuCombination($array)
    {

        $results = [[]];

        foreach ($array as $element) {
            foreach ($results as $combination) {
                array_push($results, array_merge([$element], $combination));
            }
        }

        unset($results[0]);

        $arrayMap = array_map('count', $results);
        array_multisort($arrayMap, SORT_ASC, $results);

        return $results;
    }

    /**
     * @param        $sku
     * @param string $type
     *
     * @return array
     */
    public function getFieldRange($sku, $type = 'global')
    {

        $tableName = $this->getTable('panda_search_relations_' . $type);

        $days = $this->connection->fetchCol(
            $this->connection->select()
                             ->from($tableName, [])
                             ->columns(
                                 [
                                     'distinct' => new \Zend_Db_Expr("DISTINCT($type)"),
                                 ]
                             )
                             ->where('query = ?', $sku)
                             ->order($type)
        );

        return $days;
    }

    /**
     * @param string $type
     * @param string $field
     *
     * @return array
     */
    public function getFieldRangeVenn($type = 'global', $field = 'age')
    {

        $tableName = $this->getTable('panda_search_venn_' . $type);

        $select = $this->connection->select()
                                   ->from($tableName, [])
                                   ->columns(
                                       [
                                           'distinct' => new \Zend_Db_Expr("DISTINCT($field)"),
                                       ]
                                   )
                                   ->order($field);

        $days = $this->connection->fetchCol($select);

        return $days;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function reindexSearchperformance()
    {

        $types = $this->getTypes();

        $segments = $this->segmentsCollectionFactory->create()
                                                    ->addFieldToFilter('products_relations', 1);

        $types = $this->indexer->getTypesToReindex($types, self::INDEXER_NAME);
        $this->indexer->updateIndexStatus(Indexer::STATUS_WORKING, self::INDEXER_NAME);

        foreach ($types as $type) {
            $this->rebuildPerformanceItem($type);
            $this->rebuildRelations($type);
            $this->rebuildVenn($type);

            /** @var \Licentia\Equity\Model\Segments $segment */
            foreach ($segments as $segment) {
                $this->rebuildPerformanceItem($type, $segment->getId());
                $this->rebuildRelations($type, $segment->getId());
                $this->rebuildVenn($type, $segment->getId());
            }

            $this->indexer->updateIndex($type, 0, self::INDEXER_NAME);
        }

        $this->indexer->updateIndexStatus(Indexer::STATUS_VALID, self::INDEXER_NAME);

        return $this;
    }

    /**
     * @param string $type
     * @param null   $segmentId
     *
     * @return $this
     * @throws \Exception
     */
    public function rebuildPerformanceItem($type = 'global', $segmentId = null)
    {

        $type = strtolower($type);

        if (!in_array($type, $this->getTypes())) {
            throw new \Exception('Invalid Type:' . $type);
        }

        $resource = $this->getResource();
        $connection = $resource->getConnection();

        $pandaCustomerKpisTable = $resource->getTable('panda_customers_kpis');
        $pandaSegmentsRecordsTable = $resource->getTable('panda_segments_records');
        $pandaSalesAddressTable = $resource->getTable('sales_order_address');
        $pandaMetadataSearches = $resource->getTable('panda_segments_metadata_searches');
        $salesTable = $resource->getTable('sales_order');

        $select = $connection->select();

        $selectColumns['month'] = new \Zend_Db_Expr("MONTH(s.created_at)");
        $selectColumns['year'] = new \Zend_Db_Expr("YEAR(s.created_at)");
        $selectColumns['date'] = new \Zend_Db_Expr("date_format(s.created_at,'%Y-%m-%d')");
        $selectColumns['day'] = new \Zend_Db_Expr("date_format(s.created_at,'%d')");
        $selectColumns['weekday'] = new \Zend_Db_Expr("date_format(s.created_at,'%w')");
        $selectColumns['day_year'] = new \Zend_Db_Expr("date_format(s.created_at,'%j')");
        $selectColumns['query'] = new \Zend_Db_Expr("query");
        $selectColumns['total'] = new \Zend_Db_Expr("COUNT(*)");

        $mainTable = 'panda_search_performance_' . $type;

        $mainTable = $resource->getTable($mainTable);

        $select->from(['s' => $pandaMetadataSearches], []);

        if (!$this->shouldWeRebuildForType($type)) {
            if ($output = $this->getData('consoleOutput')) {
                if ($output instanceof OutputInterface) {
                    $extra = '';
                    if ($segmentId) {
                        $extra .= ' / SEG ID:' . $segmentId;
                    }

                    $extra .= ' - ' . $this->pandaHelper->gmtDate();

                    $output->writeln("Finished (No " . $mainTable . "): " . $mainTable . " / " . $extra);
                }
            }
        }

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

            $selectColumns['region'] = new \Zend_Db_Expr("addr.region");
            $selectColumns['country'] = new \Zend_Db_Expr("addr.country_id");
        }

        if ($type == 'age') {
            $selectColumns['age_one'] = new \Zend_Db_Expr(\Licentia\Reports\Helper\Data::getAgeMySQLGroup($this->getMySQLVersion()));
        }

        if ($type == 'male' || $type == 'female') {
            $select->joinInner(
                ['k' => $this->getTable('panda_customers_kpis')],
                "s.email = k.email_meta AND (gender='$type' OR (gender IS NULL and predicted_gender='$type'))",
                []
            );
        }

        $select->join(
            ['kpi' => $pandaCustomerKpisTable],
            "(kpi.email_meta = s.email OR s.customer_id=kpi.customer_id)",
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
        }

        if ($type == 'country' || $type == 'region') {
            $select->join(
                ['sa' => $salesTable],
                's.email=sa.customer_email',
                []
            );

            $select->join(
                ['addr' => $pandaSalesAddressTable],
                "addr.parent_id=sa.entity_id AND addr.address_type='billing'",
                []
            );
        }

        if ($segmentId) {
            $select->where("$pandaSegmentsRecordsTable.segment_id = ? ", $segmentId);
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

        $select->group('date');
        $select->order('date');

        $select->columns($selectColumns);

        if ($this->minSearchNumber) {
            $select->having('total > ? ', $this->minSearchNumber);
        }

        $result = $connection->fetchAll($select);
        $totalResults = count($result);

        $data = [];
        for ($i = 0; $i < $totalResults; $i++) {
            $current = $result[$i];

            $data[$i] = $current;

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

            if ($type == 'age') {
                $data[$i]['age'] = $current['age_one'];
            }
        }

        if (!$segmentId) {
            $connection->delete($mainTable, "segment_id IS NULL");
        } else {
            $connection->delete($mainTable, ['segment_id=?' => $segmentId]);
        }

        foreach ($data as $insert) {
            $insert = array_intersect_key($insert, $this->describeTable($mainTable));

            try {
                $connection->insert($mainTable, $insert);
            } catch (\Exception $e) {
                $this->pandaHelper->logException($e);
            }
        }

        if ($output = $this->getData('consoleOutput')) {
            if ($output instanceof OutputInterface) {
                $extra = '';
                if ($segmentId) {
                    $extra = ' / SEG ID:' . $segmentId;
                }

                $extra .= ' - ' . $this->pandaHelper->gmtDate();

                $output->writeln("Finished: " . $mainTable . $extra);
            }
        }

        return $this;
    }

    /**
     * @param bool   $segmentId
     * @param string $type
     */
    public function rebuildRelations($type = 'global', $segmentId = false)
    {

        $table = 'panda_search_relations_' . $type;

        $mainTable = $this->getTable($table);
        $salesTable = $this->getTable('sales_order');
        $searchMetadata = $this->getTable('panda_segments_metadata_searches');

        if (!$segmentId) {
            $this->connection->delete($mainTable, ['segment_id IS NULL']);
        }

        if ($segmentId) {
            $this->connection->delete($mainTable, ['segment_id=?' => $segmentId]);
        }

        $loops = [0];

        if (!$this->shouldWeRebuildForType($type)) {
            if ($output = $this->getData('consoleOutput')) {
                if ($output instanceof OutputInterface) {
                    $extra = '';
                    if ($segmentId) {
                        $extra .= ' / SEG ID:' . $segmentId;
                    }

                    $extra .= ' - ' . $this->pandaHelper->gmtDate();

                    $output->writeln("Finished (No " . $mainTable . "): " . $mainTable . " / " . $extra);
                }
            }
        }

        if ($type == 'age') {
            $loops = self::POSSIBLE_AGE_RANGES;
        }

        if ($type == 'region') {
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
                $loops = $regions;
            } else {
                $select = $this->connection->select();

                $select->from(['s' => $searchMetadata], []);
                $select->joinInner(['o' => $salesTable], 's.email = o.customer_email', []);
                $select->joinInner(
                    ['ad' => $this->getTable('sales_order_address')],
                    "o.entity_id = ad.parent_id AND ad.address_type = 'billing' ",
                    ["DISTINCT(region_id)"]
                );

                $loops = $this->connection->fetchCol($select);

                $loops = array_filter($loops);
            }
        }

        if ($type == 'country') {
            $countries = array_filter(
                explode(
                    ',',
                    $this->scopeConfig->getValue('panda_equity/reports/country', ScopeInterface::SCOPE_WEBSITE)
                )
            );

            if ($countries) {
                $loops = $countries;
            } else {
                $select = $this->connection->select();

                $select->from(['s' => $searchMetadata], []);
                $select->joinInner(['o' => $salesTable], 's.email = o.customer_email', []);
                $select->joinInner(
                    ['ad' => $this->getTable('sales_order_address')],
                    "o.entity_id = ad.parent_id AND ad.address_type = 'billing' ",
                    ['DISTINCT(country_id)']
                );

                $loops = $this->connection->fetchCol($select);

                $loops = array_filter($loops);
            }
        }

        $selectQueries = $this->connection->select()
                                          ->from($searchMetadata, ['query'])
                                          ->group('query')
                                          ->order('count(*) DESC');

        if ($this->minSearchNumber) {
            $selectQueries->having('count(*) > ?', $this->minSearchNumber);
        }

        $queries = $this->connection->fetchCol($selectQueries);

        foreach ($queries as $query) {
            foreach ($loops as $loop) {
                $select = $this->connection->select();
                $select->from(['s' => $searchMetadata], ['total' => 'COUNT(*)', 'query']);

                $select->where('s.query!=?', $query);

                if ($type == 'country') {
                    $select->joinInner(['o' => $salesTable], 's.email = o.customer_email', []);
                    $select->joinInner(
                        ['ad' => $this->getTable('sales_order_address')],
                        "o.entity_id = ad.parent_id AND ad.address_type = 'billing' ",
                        ['country' => 'country_id']
                    );

                    $select->where('country_id=?', $loop);
                }

                if ($type == 'region') {
                    $select->joinInner(['o' => $salesTable], 's.email = o.customer_email', []);
                    $select->joinInner(
                        ['ad' => $this->getTable('sales_order_address')],
                        "o.entity_id = ad.parent_id AND ad.address_type = 'billing' AND LENGTH(region) > 1 ",
                        ['region' => "CONCAT(region, ' - ', country_id)"]
                    );
                    $select->where("region_id =?", $loop);
                }

                if ($type == 'male' || $type == 'female') {
                    $select->joinInner(
                        ['k' => $this->getTable('panda_customers_kpis')],
                        "s.email = k.email_meta AND (gender='$type' OR (gender IS NULL and predicted_gender='$type'))",
                        []
                    );
                }

                if ($type == 'age') {
                    $select->joinInner(
                        ['k' => $this->getTable('panda_customers_kpis')],
                        's.email = k.email_meta AND age>=18',
                        []
                    );
                    $newColumns = [
                        'age' => new \Zend_Db_Expr(\Licentia\Reports\Helper\Data::getAgeMySQLGroup($this->getMySQLVersion())),
                    ];

                    $select->columns($newColumns);
                    $select->where('age=?', $loop);
                }

                if ($segmentId) {
                    $select->joinInner(
                        ['p' => $this->getTable('panda_segments_records')],
                        's.email = p.email',
                        []
                    );
                }

                if ($segmentId) {
                    $select->where('p.segment_id=?', $segmentId);
                }

                $select->where("s.email IN (SELECT email from $searchMetadata WHERE query=?) ", $query);

                $select->group('s.query');
                $select->order('COUNT(*) DESC');
                $select->limit(50);

                $result = $this->connection->fetchAll($select);

                if (!$result) {
                    continue;
                }

                $data = [];

                if ($segmentId) {
                    $data['segment_id'] = $segmentId;
                }

                $data['query'] = $query;
                $data['total'] = $this->connection->fetchOne(
                    $this->connection->select()
                                     ->from($searchMetadata, [new \Zend_Db_Expr('COUNT(*)')])
                                     ->where('query=?', (string) $query)
                );

                if (array_key_exists('age', $result[0])) {
                    $data['age'] = $loop;
                }
                if (array_key_exists('country', $result[0])) {
                    $data['country'] = $loop;
                }
                if (array_key_exists('region', $result[0])) {
                    $data['region'] = $loop;
                }

                $i = 1;
                foreach ($result as $entry) {
                    $data['related_' . $i] = $entry['query'];
                    $data['related_total_' . $i] = $entry['total'];

                    $i++;
                }

                $this->connection->insert($mainTable, $data);
            }
        }

        if ($output = $this->getData('consoleOutput')) {
            if ($output instanceof OutputInterface) {
                $extra = '';
                if ($segmentId) {
                    $extra = ' / SEG ID:' . $segmentId;
                }

                $extra .= ' - ' . $this->pandaHelper->gmtDate();

                $output->writeln("Finished: " . $mainTable . $extra);
            }
        }

        return;
    }

    /**
     * @param bool   $segmentId
     * @param string $type
     */
    public function rebuildVenn($type = 'global', $segmentId = false)
    {

        $table = 'panda_search_venn_' . $type;
        $tableValues = 'panda_search_relations_' . $type;

        $mainTable = $this->getTable($table);
        $salesTable = $this->getTable('sales_order');
        $searchMetadata = $this->getTable('panda_segments_metadata_searches');

        if (!$segmentId) {
            $this->connection->delete($mainTable, ['segment_id IS NULL']);
        }

        if ($segmentId) {
            $this->connection->delete($mainTable, ['segment_id=?' => $segmentId]);
        }

        $loops = [0];

        if ($type == 'age') {
            $loops = self::POSSIBLE_AGE_RANGES;
        }

        if ($type == 'region') {
            $regions = array_filter(
                explode(
                    ',',
                    $this->scopeConfig->getValue('panda_equity/reports/region', ScopeInterface::SCOPE_WEBSITE)
                )
            );

            if ($regions) {
                $loops = $regions;
            } else {
                $select = $this->connection->select();
                $select->from($tableValues, ['DISTINCT(region)']);
                $loops = $this->connection->fetchCol($select);
                $loops = array_filter($loops);
            }
        }

        if ($type == 'country') {
            $countries = array_filter(
                explode(
                    ',',
                    $this->scopeConfig->getValue('panda_equity/reports/country', ScopeInterface::SCOPE_WEBSITE)
                )
            );

            if ($countries) {
                $loops = $countries;
            } else {
                $select = $this->connection->select();
                $select->from($tableValues, ['DISTINCT(country)']);
                $loops = $this->connection->fetchCol($select);
                $loops = array_filter($loops);
            }
        }

        if (!$this->shouldWeRebuildForType($type)) {
            if ($output = $this->getData('consoleOutput')) {
                if ($output instanceof OutputInterface) {
                    $extra = '';
                    if ($segmentId) {
                        $extra .= ' / SEG ID:' . $segmentId;
                    }

                    $extra .= ' - ' . $this->pandaHelper->gmtDate();

                    $output->writeln("Finished (No " . $mainTable . "): " . $mainTable . " / " . $extra);
                }
            }
        }

        foreach ($loops as $loop) {
            $select = $this->connection->select();
            $select->from(['s' => $searchMetadata], ['total' => 'COUNT(*)', 'query'])
                   ->joinInner(['b' => $searchMetadata], 's.email = b.email', []);

            if ($type == 'country') {
                $select->joinInner(['o' => $salesTable], 's.email = o.customer_email', []);
                $select->joinInner(
                    ['ad' => $this->getTable('sales_order_address')],
                    "o.entity_id = ad.parent_id AND ad.address_type = 'billing' ",
                    ['country' => 'country_id']
                );

                $select->where('country_id=?', $loop);
            }

            if ($type == 'region') {
                $select->joinInner(['o' => $salesTable], 's.email = o.customer_email', []);
                $select->joinInner(
                    ['ad' => $this->getTable('sales_order_address')],
                    "o.entity_id = ad.parent_id AND ad.address_type = 'billing' AND LENGTH(region) > 1 ",
                    ['region' => "CONCAT(region, ' - ', country_id)"]
                );
                $select->where("region_id =?", $loop);
            }

            if ($type == 'male' || $type == 'female') {
                $select->joinInner(
                    ['k' => $this->getTable('panda_customers_kpis')],
                    "s.email = k.email_meta AND (gender='$type' OR (gender IS NULL and predicted_gender='$type'))",
                    []
                );
            }

            if ($type == 'age') {
                $select->joinInner(
                    ['k' => $this->getTable('panda_customers_kpis')],
                    's.email = k.email_meta AND age>=18',
                    []
                );
                $newColumns = [
                    'age' => new \Zend_Db_Expr(\Licentia\Reports\Helper\Data::getAgeMySQLGroup($this->getMySQLVersion())),
                ];

                $select->columns($newColumns);

                $select->where('age=?', $loop);
            }

            if ($segmentId) {
                $select->joinInner(
                    ['p' => $this->getTable('panda_segments_records')],
                    's.email = p.email',
                    []
                );
            }

            if ($segmentId) {
                $select->where('p.segment_id=?', $segmentId);
            }

            $select->group('s.query');
            $select->order('COUNT(*) DESC');

            $select->columns(['query' => 'GROUP_CONCAT(DISTINCT(b.query))']);

            $result = $this->connection->fetchAll($select);

            if (!$result) {
                continue;
            }

            foreach ($result as $item) {
                $data = [];

                if ($segmentId) {
                    $data['segment_id'] = $segmentId;
                }
                if (array_key_exists('age', $result[0])) {
                    $data['age'] = $result[0]['age'];
                }
                if (array_key_exists('country', $result[0])) {
                    $data['country'] = $result[0]['country'];
                }
                if (array_key_exists('region', $result[0])) {
                    $data['region'] = $result[0]['region'];
                }

                $data['total'] = $item['total'];
                $info['query'] = str_getcsv($item['query']);

                sort($info['query']);
                $info['query'] = array_filter($info['query']);

                $i = 1;
                foreach ($info['query'] as $value) {
                    if ($i == 50) {
                        break;
                    }

                    $data['query_' . $i] = $value;
                    $i++;
                }

                $this->connection->insert($mainTable, $data);
            }
        }

        if ($output = $this->getData('consoleOutput')) {
            if ($output instanceof OutputInterface) {
                $extra = '';
                if ($segmentId) {
                    $extra = ' / SEG ID:' . $segmentId;
                }

                $extra .= ' - ' . $this->pandaHelper->gmtDate();

                $output->writeln("Finished: " . $mainTable . $extra);
            }
        }

        return;
    }

    /**
     * @param $table
     *
     * @return string
     */
    public function getTable($table)
    {

        return $this->getResource()->getTable($table);
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
     * @param $query
     *
     * @return array
     */
    public function getPossibleCountries($query = null)
    {

        $table = $this->getTable('panda_search_performance_country');

        $select = $this->connection->select()->from($table, ['country'])->distinct();

        if ($query) {
            $select->where('query=?', $query);
        }

        return $this->connection->fetchCol($select);
    }

    /**
     * @param $query
     *
     * @return array
     */
    public function getPossibleAges($query = null)
    {

        $table = $this->getTable('panda_search_performance_age');

        $select = $this->connection->select()->from($table, ['age', 'age'])->distinct();

        if ($query) {
            $select->where('query=?', $query);
        }

        return $this->connection->fetchPairs($select);
    }

    /**
     * @param $query
     *
     * @return array
     */
    public function getRegions($query = null)
    {

        $table = $this->getTable('panda_search_performance_region');

        $select = $this->connection->select()
                                   ->from($table, ['region', new \Zend_Db_Expr("CONCAT(region,' - ',country)")])
                                   ->distinct();

        if ($query) {
            $select->where('query=?', $query);
        }

        return $this->connection->fetchpairs($select);
    }

    /**
     * @return array
     */
    public static function getGroups()
    {

        return [
            'date'    => __('Day'),
            'day'     => __('Day of Month'),
            'weekday' => __('Week Day'),
            'month'   => __('Month'),
            'year'    => __('Year'),
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
        if (isset($types['gender'])) {
            unset($types['gender']);

            $types[] = 'male';
            $types[] = 'female';
        }

        $types = array_combine($types, $types);

        return $types;
    }

    /**
     * @param $type
     *
     * @return bool
     */
    public function shouldWeRebuildForType($type)
    {

        if ($type == 'age') {
            $totalAges = $this->connection->fetchCol(
                $this->connection->select()
                                 ->from($this->getTable('panda_customers_kpis'), ['age'])
                                 ->distinct()
            );

            array_filter($totalAges);

            if (!$totalAges) {
                return false;
            }
        }

        if ($type == 'female' || $type == 'male') {
            $totalGender = $this->connection->fetchOne(
                $this->connection->select()
                                 ->from($this->getTable('panda_customers_kpis'))
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
     * @param int $number
     */
    public function dummyData($number = 2000)
    {

        $connection = $this->getResource()->getConnection();
        $words = $connection->fetchCol('select word from ' . $this->getTable('random_words'));
        $customers = $connection->fetchAll('select entity_id, email from ' . $this->getTable('customer_entity'));

        for ($i = 0; $i <= $number; $i++) {
            $customer = array_rand($customers);
            $word = array_rand($words);
            $connection->insert(
                $this->getTable('panda_segments_metadata_searches'),
                [
                    'customer_id' => $customers[$customer]['entity_id'],
                    'email'       => $customers[$customer]['email'],
                    'query'       => $words[$word],
                    'created_at'  => date('Y-m-d H:i:s', rand(strtotime('NOW -3 years'), strtotime('now'))),
                ]
            );

        }
    }
}
