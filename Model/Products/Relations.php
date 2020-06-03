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
 * @modified   03/06/20, 16:24 GMT
 *
 */

namespace Licentia\Reports\Model\Products;

use \Licentia\Reports\Model\Indexer;
use \Symfony\Component\Console\Output\OutputInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Relations
 *
 * @package Licentia\Panda\Model\Products
 */
class Relations extends \Magento\Framework\Model\AbstractModel
{

    const SQL_AGE_EXPRESSION = "IF(age IS NULL,predicted_age,CASE  
                              WHEN age >= 18 AND age <= 24 THEN '18-24'  
                              WHEN age >=25 AND age <=34 THEN '25-34'
                              WHEN age >=35 AND age <=45 THEN '35-44'
                              WHEN age >=45 AND age <= 54 THEN '45-54'  
                              WHEN age >=55 AND age <=64 THEN '55-64'  
                              WHEN age >=65 THEN '65+'   
                            END)";

    /**
     *
     */
    const PRODUCTS_VENN_TABLE_PREFIX = 'panda_products_venn_';

    /**
     *
     */
    const PRODUCTS_VENN_TABLE_PREFIX_ATTRS = 'panda_products_venn_attrs_';

    /**
     *
     */
    const PRODUCTS_RECOMMENDATIONS_TABLE_PREFIX = 'panda_products_recommendations_';

    /**
     *
     */
    const PRODUCTS_RELATIONS_TABLE_PREFIX = 'panda_products_relations_';

    /**
     *
     */
    const PRODUCTS_RELATIONS_TABLE_PREFIX_ATTRS = 'panda_products_relations_attrs_';

    /**
     *
     */
    const NUMBER_PRODUCTS_RECOMMENDATION_AFTER_PURCHASE = 10;

    /**
     *
     */
    const NUMBER_PRODUCTS_RECOMMENDATION = 25;

    /**
     *
     */
    const NUMBER_PRODUCTS_RELATED = 50;

    /**
     *
     */
    const NUMBER_PRODUCTS_RECOMMENDATION_MAIN = 10;

    /**
     *
     */
    const REPORT_TYPES = [
        'global'    => 'global',
        'male'      => 'male',
        'female'    => 'female',
        'age'       => 'age',
        'countries' => 'country',
        'regions'   => 'region',
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
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'panda_products_relations';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'products_relations';

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
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Licentia\Reports\Model\IndexerFactory
     */
    protected $indexer;

    /**
     * @var \Licentia\Reports\Model\Sales\StatsFactory
     */
    protected $salesStats;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_init(\Licentia\Reports\Model\ResourceModel\Products\Relations::class);
    }

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Relations constructor.
     *
     * @param \Licentia\Reports\Model\Sales\StatsFactory                   $statsFactory
     * @param \Licentia\Reports\Model\IndexerFactory                       $indexer
     * @param \Magento\Framework\App\Config\ScopeConfigInterface           $scopeConfigInterface
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface         $timezone
     * @param \Licentia\Equity\Model\SegmentsFactory                       $segmentsFactory
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Licentia\Reports\Helper\Data                                $pandaHelper
     * @param \Magento\Catalog\Model\ProductFactory                        $productFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Licentia\Reports\Model\Sales\StatsFactory $statsFactory,
        \Licentia\Reports\Model\IndexerFactory $indexer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Licentia\Equity\Model\SegmentsFactory $segmentsFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Licentia\Reports\Helper\Data $pandaHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->segmentsFactory = $segmentsFactory;
        $this->timezone = $timezone;
        $this->productFactory = $productFactory;
        $this->scopeConfig = $scopeConfigInterface;
        $this->pandaHelper = $pandaHelper;
        $this->indexer = $indexer->create();
        $this->salesStats = $statsFactory;

        $this->connection = $this->getResource()->getConnection();
    }

    /**
     * @throws \Exception
     */
    public function rebuildRecommendationsForYesterday()
    {

        $date = $this->timezone->date($this->pandaHelper->gmtDate())
                               ->sub(new \DateInterval('P1D'))
                               ->format('Y-m-d');

        $this->rebuildAllRecommendations($date);
    }

    /**
     * @param bool $type
     *
     * @return $this
     */
    public function rebuildOne($type = false)
    {

        $this->rebuildRecommendationItem(false, false, false, $type);
        $this->rebuildRecommendationItem(false, false, true, $type);

        $segments = $this->segmentsFactory->create()
                                          ->getCollection()
                                          ->addFieldToFilter('is_active', 1)
                                          ->addFieldToFilter('products_relations', 1);

        /** @var \Licentia\Equity\Model\Segments $segment */
        foreach ($segments as $segment) {
            $this->rebuildRecommendationItem(false, $segment->getId(), false, $type);
            $this->rebuildRecommendationItem(false, $segment->getId(), true, $type);
            $this->rebuildRecommendationsAvgDays($segment->getId(), $type);
        }

        $this->rebuildRecommendationsProductsBought();
        $this->rebuildRecommendationsProductCategories($type);
        $this->rebuildRecommendationsAvgDays(false, $type);

        return $this;
    }

    /**
     * @return Relations
     */
    public function reindexRecommendations()
    {

        return $this->rebuildAllRecommendations();
    }

    /**
     * @param bool $date
     *
     * @return $this
     */
    public function rebuildAllRecommendations($date = false)
    {

        if (!$this->getData('consoleOutput') && !$this->indexer->canReindex('recommendations')) {
            throw new \RuntimeException("Indexer status does not allow reindexing");
        }

        $types = $this->getTypes();

        $segments = $this->segmentsFactory->create()
                                          ->getCollection()
                                          ->addFieldToFilter('products_relations', 1);

        if (!$date) {
            $types = $this->indexer->getTypesToReindex($types, 'recommendations');
            $this->indexer->updateIndexStatus(Indexer::STATUS_WORKING, 'recommendations');
        }

        foreach ($types as $type) {
            $this->rebuildRecommendationItem($date, false, false, $type);
            $this->rebuildRecommendationItem($date, false, true, $type);

            /** @var \Licentia\Equity\Model\Segments $segment */
            foreach ($segments as $segment) {
                $this->rebuildRecommendationItem($date, $segment->getId(), false, $type);
                $this->rebuildRecommendationItem($date, $segment->getId(), true, $type);
            }

            if (!$date) {
                $this->indexer->updateIndex($type, 0, 'recommendations');
            }
        }

        if (!$date) {
            $this->rebuildRecommendationsProductCategories();
            $this->rebuildRecommendationsAvgDays();
            $this->rebuildRecommendationsProductsBought();

            $this->indexer->updateIndexStatus(Indexer::STATUS_VALID, 'recommendations');
        }

        return $this;
    }

    /**
     * @param bool   $date
     * @param bool   $segmentId
     * @param bool   $afterPurchase
     * @param string $type
     *
     * @return $this
     */
    public function rebuildRecommendationItem(
        $date = false,
        $segmentId = false,
        $afterPurchase = false,
        $type = 'global'
    ) {

        $table = self::PRODUCTS_RECOMMENDATIONS_TABLE_PREFIX . $type;

        $mainTable = $this->getTable($table);
        $salesItemTable = $this->getTable('sales_order_item');
        $salesTable = $this->getTable('sales_order');
        $productsTable = $this->getTable('catalog_product_entity');

        if (!$afterPurchase && !$date && !$segmentId) {
            $this->connection->delete($mainTable, ['segment_id IS NULL']);
        }

        if (!$afterPurchase && $segmentId && !$date) {
            $this->connection->delete($mainTable, ['segment_id=?' => $segmentId]);
        }

        $lastProductId = (int) $this->connection->fetchOne(
            "SELECT MAX(entity_id) FROM 
            " . $this->connection->quoteIdentifier($productsTable)
        );

        $firstProductId = (int) $this->connection->fetchOne(
            "SELECT MIN(entity_id) FROM 
            " . $this->connection->quoteIdentifier($productsTable)
        );

        $rowsToProcess = 1000;
        $start = $firstProductId;
        $end = false;
        $emptyCycles = 0;
        $hasAge = false;
        $hasCountry = false;
        $hasRegion = false;
        while (true) {
            $select = $this->connection->select();
            $select->from(['a' => $salesItemTable], [])
                   ->joinLeft(['c' => $productsTable], 'a.sku = c.sku', ['original' => 'sku', 'entity_id'])
                   ->joinInner(['b' => $salesItemTable], 'a.order_id = b.order_id AND a.sku != b.sku ', ['sku'])
                   ->joinInner(['s' => $salesTable], 's.entity_id = a.order_id ', [])
                   ->where('LENGTH(c.sku)>0');

            $select->columns(['total' => new \Zend_Db_Expr("SUM(a.qty_invoiced)")]);

            if ($type == 'country') {
                $hasCountry = true;
                $select->joinInner(
                    ['ad' => $this->getTable('sales_order_address')],
                    "s.entity_id = ad.parent_id AND ad.address_type = 'billing' ",
                    ['country' => 'country_id']
                );

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
                    $select->where('ad.country_id IN (?)', $countries);
                }
            }

            if ($type == 'regions') {
                $hasRegion = true;
                $regionsTable = $this->getTable('directory_country_region_name');
                $select->where(
                    "ad.region_id IN (SELECT DISTINCT(region_id) FROM $regionsTable)"
                )->joinInner(
                    ['ad' => $this->getTable('sales_order_address')],
                    "s.entity_id = ad.parent_id AND ad.address_type = 'billing' AND LENGTH(TRIM(region))>1 ",
                    ['country' => 'country_id', 'region' => 'TRIM(region)']
                );

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
                    $select->where('ad.region_id IN (?)', $regions);
                }
            }

            if ($type == 'male' || $type == 'female') {
                $select->joinInner(
                    ['k' => $this->getTable('panda_customers_kpis')],
                    "s.customer_email = k.email_meta AND (gender='$type' OR (gender IS NULL and predicted_gender='$type'))",
                    []
                );
            }

            if ($type == 'age') {
                $hasAge = true;
                $select->joinInner(
                    ['k' => $this->getTable('panda_customers_kpis')],
                    's.customer_email = k.email_meta AND (age>18 OR predicted_age IS NOT NULL)',
                    []
                );
                $newColumns = [
                    'b_age' => new \Zend_Db_Expr(self::SQL_AGE_EXPRESSION),
                ];

                $select->columns($newColumns);
            }

            if (!$afterPurchase) {
                $select->where("c.sku NOT IN (SELECT sku from $mainTable)");
            } else {
                $select->where("c.sku NOT IN (SELECT sku from $mainTable WHERE after_order_1 IS NOT NULL)");
            }

            if ($segmentId) {
                $select->joinInner(
                    ['p' => $this->getTable('panda_segments_records')],
                    's.customer_email = p.email',
                    []
                );
            }

            if ($date) {
                $end = true;
                $select->where("DATE_FORMAT(a.created_at,'%Y-%m-%d') = ?", $date);
            } else {
                $select->where("c.entity_id BETWEEN {$start} AND ?", ($start + $rowsToProcess));
                $select->where("c.entity_id <= ?", $lastProductId);
                $select->where("DATE_FORMAT(a.created_at,'%Y-%m-%d') >= curdate() - interval 2 year ");
            }

            if ($segmentId) {
                $select->where('p.segment_id=?', $segmentId);
            }

            $select->where('s.state NOT IN (?)', ['canceled', 'new', 'hold']);

            if ($afterPurchase) {
                $subSelect = $this->connection->select()
                                              ->from($salesItemTable, ['order_id'])
                                              ->joinInner(
                                                  $salesTable,
                                                  $salesItemTable . '.order_id = ' . $salesTable . '.entity_id',
                                                  []
                                              )
                                              ->where($salesItemTable . '.sku = a.sku')
                                              ->where($salesTable . '.customer_email = s.customer_email')
                                              ->order($salesItemTable . '.item_id ASC');

                $select->where(
                    'a.order_id = COALESCE( (' . (string) $subSelect . ' LIMIT 1,1), (' . (string) $subSelect . ' LIMIT 1) )'
                );
            }

            $select->where('a.parent_item_id IS NULL OR a.parent_item_id =?', 0);

            $select->group('b.sku')
                   ->group('c.sku')
                   ->order('c.sku')
                   ->order('COUNT(*) DESC');

            if ($type == 'age') {
                $select->group('b_age');
            }
            if ($type == 'countries') {
                $select->group('country');
            }
            if ($type == 'regions') {
                $select->group('country');
                $select->group('TRIM(region)');
            }

            $result = [];
            try {
                $result = $this->connection->fetchAll($select);
            } catch (\Exception $e) {
                $this->pandaHelper->logException($e);
            }

            if (count($result) == 0) {
                $emptyCycles++;
            } else {
                $emptyCycles = 0;
            }

            $newArray = [];
            foreach ($result as $item) {
                if (isset($item['b_age'])) {
                    $newArray[$item['original'] . '_' . $item['b_age']][] = $item;
                } elseif (array_key_exists('region', $item)) {
                    $newArray[$item['original'] . '_' . $item['country'] . '_' . $item['region']][] = $item;
                } elseif (isset($item['country'])) {
                    $newArray[$item['original'] . '_' . $item['country']][] = $item;
                } else {
                    $newArray[$item['original']][] = $item;
                }

                if ($lastProductId == $item['entity_id']) {
                    $end = true;
                }
            }

            foreach ($newArray as $skuSupport => $entry) {
                $sku = $entry[0]['original'];
                $region = '';

                $supportField = str_replace($sku . '_', '', $skuSupport);

                if ($hasRegion) {
                    $supFields = explode('_', $skuSupport);
                    $region = end($supFields);
                    $supportField = $supFields[count($supFields) - 2];
                    #$sku = implode('_', array_slice($supFields, 0, count($supFields) - 2));
                }

                if ($afterPurchase) {
                    $entry = array_slice($entry, 0, self::NUMBER_PRODUCTS_RECOMMENDATION_AFTER_PURCHASE);
                } else {
                    $entry = array_slice($entry, 0, self::NUMBER_PRODUCTS_RECOMMENDATION);
                }

                $data = [];
                $data['sku'] = $sku;
                $data['total'] = $this->connection->fetchOne(
                    $this->connection->select()
                                     ->from($salesItemTable, [new \Zend_Db_Expr('SUM(qty_invoiced)')])
                                     ->where('sku=?', (string) $sku)
                );

                if ($segmentId) {
                    $data['segment_id'] = $segmentId;
                }

                if ($hasAge) {
                    $data['age'] = $supportField;
                }

                if ($hasCountry) {
                    $data['country'] = $supportField;
                }

                if ($hasRegion) {
                    $data['country'] = $supportField;
                    $data['region'] = $region;
                }

                $i = 1;
                foreach ($entry as $add) {
                    if ($afterPurchase) {
                        $data['after_order_' . $i] = $add['sku'];
                        $data['after_order_total_' . $i] = $add['total'];
                    } else {
                        $data['related_' . $i] = $add['sku'];
                        $data['related_total_' . $i] = $add['total'];
                    }
                    $i++;
                }

                try {
                    if ($date) {
                        $this->connection->delete($mainTable, ['sku =?' => (string) $sku]);
                    }

                    if ($afterPurchase) {
                        $this->connection->update($mainTable, $data, ['sku=?' => (string) $sku]);
                    } else {
                        $this->connection->insert($mainTable, $data);
                    }
                } catch (\Exception $e) {
                    $this->pandaHelper->logException($e);
                }
            }

            if ($end || $emptyCycles > 10) {
                break;
            }

            $start += $rowsToProcess;
        }

        if (!$afterPurchase) {
            $start = 0;
            $limit = 1000;

            while (true) {
                $selectTable = $this->connection->select()
                                                ->from($mainTable, ['sku'])
                                                ->limit($limit, $start);

                if ($date) {
                    $selectTable->where('updated_at=?', $date);
                }

                if ($segmentId) {
                    $select->where('segment_id=?', $segmentId);
                }

                $result = $this->connection->fetchCol($selectTable);

                foreach ($result as $item) {
                    $info = $this->connection->fetchRow(
                        "SELECT related_1,related_2,related_3,related_4,related_5,
                                    related_6,related_7,related_8,related_9,related_10 
                                FROM $mainTable 
                            WHERE sku=? ",
                        (string) $item
                    );

                    $updateData = [];

                    for ($i = 1; $i <= 3; $i++) {
                        for ($a = 1; $a <= self::NUMBER_PRODUCTS_RECOMMENDATION_MAIN; $a++) {
                            $updateData[$i]['related_main_' . $i . '_' . $a] = $info['related_' . $a];
                        }
                    }

                    try {
                        $this->connection->update($mainTable, $updateData[1], ['related_1=?' => (string) $item]);
                        $this->connection->update($mainTable, $updateData[2], ['related_2=?' => (string) $item]);
                        $this->connection->update($mainTable, $updateData[3], ['related_3=?' => (string) $item]);
                    } catch (\Exception $e) {
                        $this->pandaHelper->logException($e);
                    }
                }

                if (count($result) < $limit) {
                    break;
                }

                $start += $limit;
            }
        }

        if ($output = $this->getData('consoleOutput')) {
            if ($output instanceof OutputInterface) {
                $extra = '';
                if ($segmentId) {
                    $extra = ' / SEGID: ' . $segmentId;
                }

                if ($afterPurchase) {
                    $extra .= ' / AFTER ORDER';
                }

                $extra .= ' - ' . date('Y-m-d H:i:s');

                $output->writeln("Recommendations | Finished: " . $mainTable . $extra);
            }
        }

        return $this;
    }

    /**
     * @param bool   $segmentId
     * @param string $type
     */
    public function rebuildRecommendationsAvgDays($segmentId = false, $type = 'global')
    {

        $table = self::PRODUCTS_RECOMMENDATIONS_TABLE_PREFIX . $type;
        $mainTable = $this->getTable($table);
        $salesOrderItemTable = $this->getTable('sales_order_item');
        $salesOrderTable = $this->getTable('sales_order');
        $catalogProductEntityTable = $this->getTable('catalog_product_entity');

        $lastProductId = $this->connection->fetchOne("SELECT MAX(entity_id) FROM " . $catalogProductEntityTable);

        $rowsToProcess = 1000;
        $start = 1;
        $end = false;
        $emptyCycles = 0;
        while (true) {
            $selectTmp = $this->connection->select();
            $selectTmp->from($salesOrderItemTable, ['created_at'])
                      ->joinInner(
                          $salesOrderTable,
                          $salesOrderTable . '.entity_id = ' . $salesOrderItemTable . '.order_id ',
                          []
                      )
                      ->where($salesOrderItemTable . '.sku = i.sku')
                      ->where($salesOrderTable . '.customer_email = s.customer_email')
                      ->order($salesOrderItemTable . '.order_id DESC');

            $select = $this->connection->select();
            $select->from(
                ['i' => $salesOrderItemTable],
                [
                    'average' => new \Zend_Db_Expr(
                        "ROUND(AVG(datediff(( " . (string) $selectTmp . " LIMIT 1,1),(" . (string) $selectTmp . " LIMIT 1))))"
                    ),
                ]
            )
                   ->joinLeft(['c' => $catalogProductEntityTable], 'i.sku = c.sku', ['sku', 'entity_id'])
                   ->joinInner(['s' => $salesOrderTable], 's.entity_id = i.order_id ', [])
                   ->where('c.sku IS NOT NULL');

            if ($segmentId) {
                $select->joinLeft(['p' => $this->getTable('panda_segments_records')], 's.customer_email = p.email', []);
                $select->where('p.segment_id=?', $segmentId);
            }

            if ($type == 'countries') {
                $select->joinInner(
                    ['ad' => $this->getTable('sales_order_address')],
                    "s.entity_id = ad.parent_id AND ad.address_type = 'billing' ",
                    ['country' => 'country_id']
                );
            }

            if ($type == 'regions') {
                $regionsTable = $this->getTable('directory_country_region_name');
                $select->where("ad.region_id IN (SELECT DISTINCT(region_id) FROM $regionsTable)")
                       ->joinInner(
                           ['ad' => $this->getTable('sales_order_address')],
                           "s.entity_id = ad.parent_id AND ad.address_type = 'billing' AND LENGTH(TRIM(region))>1 ",
                           ['country' => 'country_id', 'region' => 'TRIM(region)']
                       );
            }

            if ($type == 'male' || $type == 'female') {
                $select->joinInner(
                    ['k' => $this->getTable('panda_customers_kpis')],
                    "s.customer_email = k.email_meta AND (gender='$type' OR (gender IS NULL and predicted_gender='$type'))",
                    []
                );
            }

            if ($type == 'age') {
                $select->joinInner(
                    ['k' => $this->getTable('panda_customers_kpis')],
                    's.customer_email = k.email_meta AND  (age>18 OR predicted_age IS NOT NULL)',
                    []
                );
                $newColumns = [
                    'b_age' => new \Zend_Db_Expr(self::SQL_AGE_EXPRESSION),
                ];

                $select->columns($newColumns);
            }

            $select->where("c.sku NOT IN (SELECT sku from $mainTable)");

            if ($segmentId) {
                $select->joinInner(
                    ['p' => $this->getTable('panda_segments_records')],
                    's.customer_email = p.email',
                    []
                );
            }

            $select->where("c.entity_id BETWEEN {$start} AND ?", ($start + $rowsToProcess));
            $select->where("c.entity_id <= ?", $lastProductId);
            $select->where("DATE_FORMAT(s.created_at,'%Y-%m-%d') >= curdate() - interval 2 year ");

            if ($segmentId) {
                $select->where('p.segment_id=?', $segmentId);
            }

            $select->where('s.state NOT IN (?)', ['canceled', 'new', 'hold']);

            $select->where('i.parent_item_id IS NULL OR i.parent_item_id =?', 0);

            $select->group('sku')
                   ->order('c.sku')
                   ->order('COUNT(*) DESC');

            if ($type == 'age') {
                $select->group('b_age');
            }

            if ($type == 'countries') {
                $select->group('country');
            }

            if ($type == 'regions') {
                $select->group('country');
                $select->group('TRIM(region)');
            }

            $select->having('average>0');

            $result = $this->connection->fetchAll($select);

            if (count($result) == 0) {
                $emptyCycles++;
            } else {
                $emptyCycles = 0;
            }

            foreach ($result as $item) {
                if ($lastProductId == $item['entity_id']) {
                    $end = true;
                }
            }

            foreach ($result as $entry) {
                $data = [];
                if ($segmentId) {
                    $data['segment_id'] = $segmentId;
                }

                $data['avg_order'] = $entry['average'];

                $this->connection->update($mainTable, $data, ['sku=?' => (string) $entry['sku']]);
            }

            if ($end || $emptyCycles > 10) {
                break;
            }

            $start += $rowsToProcess;
        }

        if ($output = $this->getData('consoleOutput')) {
            if ($output instanceof OutputInterface) {
                $extra = '';
                if ($segmentId) {
                    $extra = ' / SEGID: ' . $segmentId;
                }

                $extra .= ' - ' . date('Y-m-d H:i:s');

                $output->writeln("Recommendations | Finished AVG DAYS: " . $mainTable . $extra);
            }
        }
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function rebuildRecommendationsProductCategories($type = 'global')
    {

        $table = self::PRODUCTS_RECOMMENDATIONS_TABLE_PREFIX . $type;
        $mainTable = $this->getTable($table);
        $categoriesTableIndex = $this->getTable('catalog_category_product');
        $productsTable = $this->getTable('catalog_product_entity');

        $this->connection->query(
            "UPDATE $mainTable r ,
                             $productsTable c
                            SET r.category_ids =(
                                SELECT
                                    GROUP_CONCAT(category_id SEPARATOR ',')
                                FROM
                                    $categoriesTableIndex
                                WHERE
                                    $categoriesTableIndex.product_id = c.entity_id
                                GROUP BY
                                    c.entity_id
                            )
                            WHERE
                                r.sku = c.sku
                         "
        );

        if ($output = $this->getData('consoleOutput')) {
            if ($output instanceof OutputInterface) {
                $extra = ' - ' . date('Y-m-d H:i:s');

                $output->writeln("Recommendations | Finished CATS: " . $mainTable . $extra);
            }
        }

        return $this;
    }

    /**
     */
    public function rebuildRecommendationsProductsBought()
    {

        $pandaCustomersKpisTable = $this->getTable('panda_customers_kpis');
        $salesOrderItemTable = $this->getTable('sales_order_item');
        $salesOrderTable = $this->getTable('sales_order');

        $this->connection->query(
            "UPDATE $pandaCustomersKpisTable k ,
                                 $salesOrderTable s
                                SET k.sku_bought =(
                                    SELECT
                                        GROUP_CONCAT(DISTINCT(sku))
                                    FROM
                                        $salesOrderItemTable
                                    JOIN $salesOrderTable ON $salesOrderTable.entity_id = $salesOrderItemTable.order_id
                                    WHERE
                                        $salesOrderTable.state = 'complete'
                                    AND $salesOrderTable.customer_email = s.customer_email
                                    GROUP BY
                                        $salesOrderTable.customer_email
                                )
                                WHERE
                                    k.email_meta = s.customer_email
                         "
        );

        if ($output = $this->getData('consoleOutput')) {
            if ($output instanceof OutputInterface) {
                $extra = ' - ' . date('Y-m-d H:i:s');

                $output->writeln("Recommendations | Finished Products BOUGHT" . $extra);
            }
        }

        return $this;
    }

    /**
     * @param $table
     *
     * @return $this
     */
    public function schedule($table)
    {

        $exists = $this->connection->fetchRow(
            $this->connection->select()
                             ->from($this->getTable('cron_schedule'))
                             ->where('job_code=?', $table)
                             ->where('status IN (?)', ['pending', 'running'])
        );

        if (!$exists) {
            $this->connection->insert(
                $this->getTable('cron_schedule'),
                [
                    'job_code'     => $table,
                    'status'       => 'pending',
                    'created_at'   => $this->pandaHelper->gmtDate(),
                    'scheduled_at' => $this->pandaHelper->gmtDate(),
                ]
            );
        }

        return $this;
    }

    /**
     * @return Relations
     */
    public function reindexRelations()
    {

        return $this->rebuildAllTotals();
    }

    /**
     * @param bool $date
     *
     * @return $this
     */
    public function rebuildAllTotals($date = false)
    {

        if (!$this->getData('consoleOutput') && !$this->indexer->canReindex('relations')) {
            throw new \RuntimeException("Indexer status does not allow reindexing");
        }

        $types = $this->getTypes();

        if (isset($types['gender'])) {
            unset($types['gender']);

            $types[] = 'male';
            $types[] = 'female';
        }

        $segments = $this->segmentsFactory->create()
                                          ->getCollection()
                                          ->addFieldToFilter('is_active', 1)
                                          ->addFieldToFilter('products_relations', 1);

        if (!$date) {
            $types = $this->indexer->getTypesToReindex($types, 'relations');
            $this->indexer->updateIndexStatus(Indexer::STATUS_WORKING, 'relations');
        }

        foreach ($types as $type) {
            $this->rebuildTotalsRelations($date, null, $type);
            $this->rebuildTotalsAttrs($date, [], $type);

            /** @var \Licentia\Equity\Model\Segments $segment */
            foreach ($segments as $segment) {
                $this->rebuildTotalsRelations($date, $segment->getId(), $type);
            }

            if ($segments->getAllIds()) {
                $this->rebuildTotalsAttrs($date, $segments->getAllIds(), $type);
            }

            if (!$date) {
                $this->indexer->updateIndex($type, 0, 'relations');
            }
        }

        if (!$date) {
            $this->indexer->updateIndexStatus(Indexer::STATUS_VALID, 'relations');
        }

        return $this;
    }

    /**
     * @return Relations
     */
    public function rebuildAllForYesterday()
    {

        $date = $this->timezone->date($this->pandaHelper->gmtDate())
                               ->sub(new \DateInterval('P1D'))
                               ->format('Y-m-d');

        return $this->rebuildAllTotals($date);
    }

    /**
     * @param bool   $date
     * @param null   $segmentId
     * @param string $type
     *
     * @return $this
     */
    public function rebuildTotalsRelations($date = false, $segmentId = null, $type = 'global')
    {

        $table = self::PRODUCTS_RELATIONS_TABLE_PREFIX . $type;

        $mainTable = $this->getTable($table);
        $salesItemTable = $this->getTable('sales_order_item');
        $salesTable = $this->getTable('sales_order');
        $catalogProductEntityTable = $this->getTable('catalog_product_entity');
        $regionsTable = $this->getTable('directory_country_region_name');

        if (!$date && !$segmentId) {
            $this->connection->delete($mainTable, ['segment_id IS NULL']);
        }

        if ($segmentId && !$date) {
            $this->connection->delete($mainTable, ['segment_id IN(?)' => $segmentId]);
        }

        $loops = [0];

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
                $loops = $this->connection->fetchCol(
                    $this->connection->select()
                                     ->where('s.state NOT IN (?)', ['canceled', 'new', 'hold'])
                                     ->where("si.parent_item_id IS NULL OR si.parent_item_id =?", 0)
                                     ->where(
                                         $this->getTable(
                                             'sales_order_address'
                                         ) . ".region_id IN (SELECT DISTINCT(region_id) FROM $regionsTable)"
                                     )
                                     ->from(
                                         $this->getTable('sales_order_address'),
                                         ["DISTINCT(TRIM(region_id))"]
                                     )
                                     ->join(
                                         ['s' => $salesTable],
                                         $this->getTable('sales_order_address') . ".parent_id = s.entity_id",
                                         []
                                     )
                                     ->join(['si' => $salesItemTable], "si.order_id = s.entity_id", [])
                );
            }
        }

        if ($type == 'country') {
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
                $loops = $countries;
            } else {
                $loops = $this->connection->fetchCol(
                    $this->connection->select()
                                     ->where('s.state NOT IN (?)', ['canceled', 'new', 'hold'])
                                     ->where("si.parent_item_id IS NULL OR si.parent_item_id =?", 0)
                                     ->from($this->getTable('sales_order_address'), ['DISTINCT(country_id)'])
                                     ->join(
                                         ['s' => $salesTable],
                                         $this->getTable('sales_order_address') . ".parent_id = s.entity_id",
                                         []
                                     )
                                     ->join(['si' => $salesItemTable], "si.order_id = s.entity_id", [])
                );
            }
        }

        if ($type == 'age') {
            $newColumns = [
                'age' => new \Zend_Db_Expr('DISTINCT(' . self::SQL_AGE_EXPRESSION . ')'),
            ];

            $loops = $this->connection->fetchCol(
                $this->connection->select()
                                 ->where('s.state NOT IN (?)', ['canceled', 'new', 'hold'])
                                 ->where("si.parent_item_id IS NULL OR si.parent_item_id =?", 0)
                                 ->from($this->getTable('sales_order_address'), [])
                                 ->join(
                                     ['s' => $salesTable],
                                     $this->getTable('sales_order_address') . ".parent_id = s.entity_id",
                                     []
                                 )
                                 ->join(['si' => $salesItemTable], "si.order_id = s.entity_id", [])
                                 ->joinInner(
                                     ['k' => $this->getTable('panda_customers_kpis')],
                                     's.customer_email = k.email_meta AND (age>18 OR predicted_age IS NOT NULL)',
                                     $newColumns
                                 )
            );
        }

        foreach ($loops as $loop) {
            $extraInsert = [];

            $selectSkus = $this->connection->select();
            $selectSkus->from(
                ['cpe' => $catalogProductEntityTable],
                ['sku' => 'cpe.sku', 'order_id' => new \Zend_Db_Expr('GROUP_CONCAT(soi.order_id)')]
            )
                       ->joinInner(['soi' => $salesItemTable], 'soi.sku = cpe.sku', [])
                       ->group('cpe.sku');

            $selectQtys = $this->connection->select();
            $selectQtys->from(
                ['cpe' => $catalogProductEntityTable],
                [
                    'order_id' => 'soi.order_id',
                    'info'     => new \Zend_Db_Expr("GROUP_CONCAT(CONCAT(cpe.sku,'|',soi.qty_ordered))"),
                ]
            )
                       ->joinInner(['soi' => $salesItemTable], 'soi.sku = cpe.sku', [])
                       ->group('soi.order_id');

            $selectSkus->joinInner(['s' => $salesTable], 's.entity_id=soi.order_id', []);
            $selectQtys->joinInner(['s' => $salesTable], 's.entity_id=soi.order_id', []);

            if ($type == 'country') {
                $selectQtys->joinInner(
                    ['ad' => $this->getTable('sales_order_address')],
                    "s.entity_id = ad.parent_id AND ad.address_type = 'billing' ",
                    ['country' => 'country_id']
                );
                $selectSkus->joinInner(
                    ['ad' => $this->getTable('sales_order_address')],
                    "s.entity_id = ad.parent_id AND ad.address_type = 'billing' ",
                    ['country' => 'country_id']
                );

                $selectSkus->where('ad.country_id=?', $loop);
                $selectQtys->where('ad.country_id=?', $loop);

                $extraInsert['country'] = $loop;
            }

            if ($type == 'region') {
                $regionsTable = $this->getTable('directory_country_region_name');
                $selectQtys->where("ad.region_id IN (SELECT DISTINCT(region_id) FROM $regionsTable)")
                           ->joinInner(
                               ['ad' => $this->getTable('sales_order_address')],
                               "s.entity_id = ad.parent_id AND ad.address_type = 'billing' AND LENGTH(TRIM(region))>1 ",
                               ['region' => "CONCAT(TRIM(region),' - ', country_id)"]
                           );
                $selectSkus->where("ad.region_id IN (SELECT DISTINCT(region_id) FROM $regionsTable)")
                           ->joinInner(
                               ['ad' => $this->getTable('sales_order_address')],
                               "s.entity_id = ad.parent_id AND ad.address_type = 'billing' AND LENGTH(TRIM(region))>1 ",
                               ['region' => "CONCAT(TRIM(region),' - ', country_id)"]
                           );

                $selectSkus->where("TRIM(region_id)=?", $loop);
                $selectQtys->where("TRIM(region_id)=?", $loop);

                $extraInsert['region'] = $loop;
            }

            if ($type == 'male' || $type == 'female') {
                $selectQtys->joinInner(
                    ['k' => $this->getTable('panda_customers_kpis')],
                    "s.customer_email = k.email_meta AND (gender='$type' OR (gender IS NULL and predicted_gender='$type'))",
                    []
                );
                $selectSkus->joinInner(
                    ['k' => $this->getTable('panda_customers_kpis')],
                    "s.customer_email = k.email_meta AND (gender='$type' OR (gender IS NULL and predicted_gender='$type'))",
                    []
                );
            }

            if ($type == 'age') {
                if (!in_array($loop, self::POSSIBLE_AGE_RANGES)) {
                    continue;
                }

                $selectQtys->joinInner(
                    ['k' => $this->getTable('panda_customers_kpis')],
                    's.customer_email = k.email_meta AND (age>18 OR predicted_age IS NOT NULL)',
                    []
                );

                $selectSkus->joinInner(
                    ['k' => $this->getTable('panda_customers_kpis')],
                    's.customer_email = k.email_meta AND (age>18 OR predicted_age IS NOT NULL)',
                    []
                );
                $selectSkus->where(
                    new \Zend_Db_Expr(self::SQL_AGE_EXPRESSION) . '=?',
                    $loop
                );
                $selectQtys->where(
                    new \Zend_Db_Expr(self::SQL_AGE_EXPRESSION) . '=?',
                    $loop
                );

                $extraInsert['age'] = $loop;
            }

            if ($segmentId) {
                $selectQtys->joinInner(
                    ['p' => $this->getTable('panda_segments_records')],
                    's.customer_email = p.email',
                    []
                );
                $selectSkus->joinInner(
                    ['p' => $this->getTable('panda_segments_records')],
                    's.customer_email = p.email',
                    []
                );

                $selectQtys->where('p.segment_id =?', $segmentId);
                $selectSkus->where('p.segment_id =?', $segmentId);

                $extraInsert['segment_id'] = $segmentId;
            }

            if ($date) {
                $selectQtys->where("DATE_FORMAT(s.created_at,'%Y-%m-%d') = ?", $date);
                $selectSkus->where("DATE_FORMAT(s.created_at,'%Y-%m-%d') = ?", $date);
            }

            $selectQtys->where('s.state NOT IN (?)', ['canceled', 'new', 'hold']);
            $selectQtys->where('soi.parent_item_id IS NULL OR soi.parent_item_id =?', 0);

            $selectSkus->where('s.state NOT IN (?)', ['canceled', 'new', 'hold']);
            $selectSkus->where('soi.parent_item_id IS NULL OR soi.parent_item_id =?', 0);

            $skus = $this->connection->fetchPairs($selectSkus);
            $qtys = $this->connection->fetchPairs($selectQtys);

            foreach ($skus as $sku => $orders) {
                $data = [];

                $orderIds = explode(',', $orders);
                $orderIds = array_filter($orderIds);
                $orderIds = array_unique($orderIds);

                foreach ($orderIds as $orderId) {
                    if (!isset($qtys[$orderId])) {
                        continue;
                    }

                    $t = explode(',', $qtys[$orderId]);

                    foreach ($t as $item) {
                        $dataInfo = explode('|', $item);

                        if (!isset($data[$dataInfo[0]])) {
                            $data[$dataInfo[0]] = 0;
                        }

                        if (isset($dataInfo[1])) {
                            $data[$dataInfo[0]] += (int) $dataInfo[1];
                        }
                    }
                }

                unset($data[$sku]);
                arsort($data);
                $total = array_sum($data);
                $data = array_slice($data, 0, 50, true);

                $insert = [];
                $insert['sku'] = $sku;
                $insert['total'] = $total;

                $i = 1;
                foreach ($data as $key => $item) {
                    $insert['related_' . $i] = $key;
                    $insert['related_total_' . $i] = $item;

                    $i++;
                }

                $insert = array_merge($insert, $extraInsert);

                $this->connection->insert($mainTable, $insert);
            }

            unset($qtys, $skus);
        }

        if ($output = $this->getData('consoleOutput')) {
            if ($output instanceof OutputInterface) {
                $extra = '';

                if ($segmentId) {
                    $extra .= 'SEGID: ' . $segmentId;
                }

                $extra .= ' - ' . date('Y-m-d H:i:s');

                $output->writeln("ProductRelations | Finished: " . $mainTable . $extra);
            }
        }

        return $this;
    }

    /**
     * @param bool   $date
     * @param array  $segmentIds
     * @param string $type
     *
     * @return $this
     */
    public function rebuildTotalsAttrs($date = false, $segmentIds = [], $type = 'global')
    {

        $table = self::PRODUCTS_RELATIONS_TABLE_PREFIX_ATTRS . $type;

        $mainTable = $this->getTable($table);
        $salesItemTable = $this->getTable('sales_order_item');
        $salesTable = $this->getTable('sales_order');

        if (!$date && !$segmentIds) {
            $this->connection->delete($mainTable, ['segment_id IS NULL']);
        }

        if ($segmentIds && !$date) {
            $this->connection->delete($mainTable, ['segment_id IN(?)' => $segmentIds]);
        }

        $attributesConfig = $this->scopeConfig->getValue(
            'panda_equity/reports/attributes',
            ScopeInterface::SCOPE_WEBSITE
        );

        if (!$attributesConfig) {
            $attributeCodes = [];
        } else {
            $attributeCodes = explode(',', $attributesConfig);
        }

        foreach ($attributeCodes as $attributeCode) {
            $skus = $this->getDistinctAttributesValues($attributeCode);
            $skus = array_keys($skus);

            foreach ($skus as $sku) {
                $insertGroup = false;

                $select = $this->connection->select();

                $select->join(['e' => $this->getTable('catalog_product_entity')], "e.sku = a.sku", []);
                $col = $this->productFactory->create()
                                            ->getCollection()
                                            ->addAttributeToFilter($attributeCode, ['neq' => 100]);

                $joinsAttributes = $col->getSelect()->getPart('from');
                unset($joinsAttributes['e']);

                $keyName = key($joinsAttributes);

                $select->where($keyName . '.value IS NOT NULL');

                $select->join(
                    [$keyName => $joinsAttributes[$keyName]['tableName']],
                    $joinsAttributes[$keyName]['joinCondition'],
                    []
                );

                $columns = $select->getPart('columns');

                foreach ($columns as $key => $column) {
                    if (isset($column[2]) && $column[2] == 'sku') {
                        unset($columns[$key]);
                    }
                }

                $select->columns(['sku' => new \Zend_Db_Expr("$keyName.value")]);

                $select->from(['a' => $salesItemTable], [])
                       ->joinInner(['s' => $salesTable], 's.entity_id = a.order_id ', []);

                $select->columns(['total' => new \Zend_Db_Expr("SUM(a.qty_invoiced)")]);

                $subSelect = $this->connection->select();
                $subSelect->from(['a1' => $salesItemTable], ['order_id'])
                          ->where($keyName . '.value=?', (string) $sku);
                $subSelect->where($keyName . '.value IS NOT NULL');
                $subSelect->join(['e' => $this->getTable('catalog_product_entity')], "e.sku = a1.sku", []);
                $subSelect->join(
                    [$keyName => $joinsAttributes[$keyName]['tableName']],
                    $joinsAttributes[$keyName]['joinCondition'],
                    []
                );

                $select->where('a.order_id IN (?)', $subSelect);

                if ($type == 'countries') {
                    $select->joinInner(
                        ['ad' => $this->getTable('sales_order_address')],
                        "s.entity_id = ad.parent_id AND ad.address_type = 'billing' ",
                        ['country' => 'country_id']
                    );

                    $insertGroup = 'country';
                    $select->group($insertGroup);
                }

                if ($type == 'regions') {
                    $regionsTable = $this->getTable('directory_country_region_name');
                    $select->where("ad.region_id IN (SELECT DISTINCT(region_id) FROM $regionsTable)")
                           ->joinInner(
                               ['ad' => $this->getTable('sales_order_address')],
                               "s.entity_id = ad.parent_id AND ad.address_type = 'billing' AND LENGTH(TRIM(region))>1 ",
                               ['region' => "CONCAT(TRIM(region),' - ', country_id)"]
                           );

                    $insertGroup = 'region';
                    $select->group($insertGroup);
                }

                if ($type == 'male' || $type == 'female') {
                    $select->joinInner(
                        ['k' => $this->getTable('panda_customers_kpis')],
                        "s.customer_email = k.email_meta AND (gender='$type' OR (gender IS NULL and predicted_gender='$type'))",
                        []
                    );
                }

                if ($type == 'age') {
                    $select->joinInner(
                        ['k' => $this->getTable('panda_customers_kpis')],
                        's.customer_email = k.email_meta AND (age>18 OR predicted_age IS NOT NULL)',
                        []
                    );

                    $newColumns = [
                        'age' => new \Zend_Db_Expr(self::SQL_AGE_EXPRESSION),
                    ];

                    $select->columns($newColumns);

                    $insertGroup = 'age';
                    $select->group($insertGroup);
                }

                if ($segmentIds) {
                    $select->joinLeft(
                        ['p' => $this->getTable('panda_segments_records')],
                        's.customer_email = p.email',
                        []
                    );

                    $select->columns(['segment_id' => 'p.segment_id']);
                    $select->where('p.segment_id IN (?)', $segmentIds);
                    $select->group('segment_id');
                } else {
                    $select->columns(['segment_id' => new \Zend_Db_Expr('NULL')]);
                }

                if ($date) {
                    $select->where("DATE_FORMAT(a.created_at,'%Y-%m-%d') = ?", $date);
                }

                $select->where('s.state NOT IN (?)', ['canceled', 'new', 'hold']);

                $select->where('a.parent_item_id IS NULL OR a.parent_item_id =?', 0);

                $select->group($keyName . '.value')
                       ->order('segment_id ASC');

                if ($insertGroup) {
                    $select->order($insertGroup);
                }

                $select->order('SUM(a.qty_invoiced) DESC');

                #$select->limit(self::NUMBER_PRODUCTS_RELATED);

                $result = $this->connection->fetchAll($select);

                if (!$result) {
                    continue;
                }

                $data = [];
                $metadata = [];

                $metadata['sku'] = $sku;
                $metadata['attribute_code'] = $attributeCode;

                foreach ($result as $key => $entry) {
                    if ($entry['sku'] == $sku) {
                        $metadata['total'] = $entry['total'];
                        unset($result[$key]);
                        break;
                    }
                }

                $result = array_values($result);

                if (!$result) {
                    continue;
                }

                $metadata['total'] = $this->connection->fetchOne(
                    $this->connection->select()
                                     ->from($salesItemTable, [new \Zend_Db_Expr('SUM(qty_invoiced)')])
                                     ->where('s.state NOT IN (?)', ['canceled', 'new', 'hold'])
                                     ->joinInner(['s' => $salesTable], 's.entity_id = order_id ', [])
                                     ->where('sku=?', (string) $sku)
                );

                $i = 1;
                $iG = [];
                $segmentsToInsert = [];
                foreach ($result as $entry) {
                    $segmentsToInsert[] = (int) $entry['segment_id'];

                    if (!isset($previousSegmentId) || (int) $entry['segment_id'] != $previousSegmentId) {
                        $i = 1;
                        $iG = [];
                    }

                    $a = $i;
                    if ($insertGroup) {
                        if (!isset($iG[$entry[$insertGroup]])) {
                            $iG[$entry[$insertGroup]] = 1;
                        }

                        $a = $iG[$entry[$insertGroup]];
                    }

                    if ($a > 50) {
                        continue;
                    }

                    if ($insertGroup) {
                        $data[(int) $entry['segment_id']][$entry[$insertGroup]]['related_' . $a] = $entry['sku'];
                        $data[(int) $entry['segment_id']][$entry[$insertGroup]]['related_total_' . $a] = $entry['total'];
                    } else {
                        $metadata['seg'][(int) $entry['segment_id']]['related_' . $a] = $entry['sku'];
                        $metadata['seg'][(int) $entry['segment_id']]['related_total_' . $a] = $entry['total'];
                    }

                    if ($insertGroup) {
                        $iG[$entry[$insertGroup]] += 1;
                    }

                    $previousSegmentId = (int) $entry['segment_id'];

                    $i++;
                }

                $segmentsToInsert = array_unique($segmentsToInsert);

                if ($date) {
                    $this->connection->delete($mainTable, ['sku =?' => (string) $sku]);
                }

                if ($insertGroup) {
                    foreach ($segmentsToInsert as $segmentId) {
                        foreach ($data[$segmentId] as $fieldName => $values) {
                            $values['segment_id'] = $segmentId == 0 ? new \Zend_Db_Expr('NULL') : $segmentId;
                            $values['sku'] = $metadata['sku'];
                            $values['total'] = $metadata['total'];
                            $values['attribute_code'] = $metadata['attribute_code'];
                            $values[$insertGroup] = $fieldName;

                            $this->connection->insert($mainTable, $values);
                        }
                    }
                } else {
                    foreach ($segmentsToInsert as $segmentId) {
                        $data = [];
                        $data['segment_id'] = $segmentId == 0 ? new \Zend_Db_Expr('NULL') : $segmentId;
                        $data['sku'] = $metadata['sku'];
                        $data['total'] = $metadata['total'];
                        $data['attribute_code'] = $metadata['attribute_code'];

                        $data = array_merge($data, $metadata['seg'][$segmentId]);

                        $this->connection->insert($mainTable, $data);
                    }
                }
            }
        }

        if ($output = $this->getData('consoleOutput')) {
            if ($output instanceof OutputInterface) {
                $extra = ' - ' . date('Y-m-d H:i:s');

                $output->writeln("ProductRelations | Finished: " . $mainTable . $extra);
            }
        }

        return $this;
    }

    /**
     * @param $attributeCode
     *
     * @return array
     */
    public function getDistinctAttributesValues($attributeCode)
    {

        $select = $this->connection->select();

        $select->from(['ea' => $this->getTable('eav_attribute')], [])
               ->joinLeft(['eao' => $this->getTable('eav_attribute_option')], 'eao.attribute_id = ea.attribute_id', [])
               ->joinLeft(
                   ['eaov' => $this->getTable('eav_attribute_option_value')],
                   'eaov.option_id = eao.option_id',
                   ['option_id', 'value']
               )
               ->where('ea.attribute_code =?', $attributeCode)
               ->where('eaov.store_id = 0');

        return $this->connection->fetchPairs($select);
    }

    /**
     * @return array
     */
    public function getPossibleAttributes()
    {

        $attributes = $this->scopeConfig->getValue(
            'panda_equity/reports/attributes',
            ScopeInterface::SCOPE_WEBSITE
        );

        if (!$attributes) {
            return [];
        }

        $attributes = explode(',', $attributes);

        $select = $this->connection->select();

        $select->from(['ea' => $this->getTable('eav_attribute')], ['attribute_code', 'frontend_label'])
               ->where('ea.attribute_code IN(?)', $attributes);

        return $this->connection->fetchPairs($select);
    }

    /**
     * @param        $sku
     * @param bool   $attributes
     * @param string $attributeCode
     *
     * @return array
     */
    public function getStatsCollection($sku, $attributes = false, $attributeCode = '')
    {

        $collection = [];

        $auxFields = ['age', 'countries', 'regions', 'gender'];
        $types = self::REPORT_TYPES;

        foreach ($types as $type) {
            if (in_array($type, $auxFields)) {
                $ages = $this->getFieldRange($sku, $type, $type, $attributes, $attributeCode);
            } else {
                $ages = [0];
            }

            $order = '';
            if ($type == 'regions') {
                $order = 'region';
            } elseif ($type == 'countries') {
                $order = 'country';
            } elseif ($type == 'age') {
                $order = 'age';
            }

            if ($attributes) {
                $tableName = $this->getTable(self::PRODUCTS_RELATIONS_TABLE_PREFIX_ATTRS . $type);
            } else {
                $tableName = $this->getTable(self::PRODUCTS_RELATIONS_TABLE_PREFIX . $type);
            }

            foreach ($ages as $age) {
                $select = $this->connection->select()
                                           ->from($tableName)
                                           ->where('sku=?', (string) $sku)
                                           ->order($order);

                if (in_array($type, $auxFields) && $age !== 0) {
                    if ($type == 'regions') {
                        $fieldSelect = 'region';
                    } elseif ($type == 'countries') {
                        $fieldSelect = 'country';
                    } else {
                        $fieldSelect = $type;
                    }

                    $select->where($fieldSelect . '=?', $age);
                }

                /** @todo adicionar opção na view para filter por segmento */
                $select->where('segment_id IS NULL');

                if ($attributes) {
                    $select->where('attribute_code=?', $attributeCode);
                }

                $collection[$type][$sku][$age] = $this->connection->fetchRow($select);
            }
        }

        return $collection;
    }

    /**
     * @param        $sku
     *
     * @return array
     */
    public function getRecommendationsCollection($sku)
    {

        $collection = [];

        $auxFields = ['age', 'countries', 'regions', 'gender'];
        $types = self::REPORT_TYPES;

        foreach ($types as $type) {
            if (in_array($type, $auxFields)) {
                $ages = $this->getFieldRange($sku, $type, $type);
            } else {
                $ages = [0];
            }

            $order = '';
            if ($type == 'regions') {
                $order = 'region';
            } elseif ($type == 'countries') {
                $order = 'country';
            } elseif ($type == 'age') {
                $order = 'age';
            }

            $tableName = $this->getTable(self::PRODUCTS_RECOMMENDATIONS_TABLE_PREFIX . $type);

            foreach ($ages as $age) {
                $select = $this->connection->select()
                                           ->from($tableName)
                                           ->where('sku=?', (string) $sku)
                                           ->order($order);

                if (in_array($type, $auxFields) && $age !== 0) {
                    if ($type == 'regions') {
                        $fieldSelect = 'region';
                    } elseif ($type == 'countries') {
                        $fieldSelect = 'country';
                    } else {
                        $fieldSelect = $type;
                    }

                    $select->where($fieldSelect . '=?', $age);
                }

                $collection[$type][$sku][$age] = $this->connection->fetchRow($select);
            }
        }

        return $collection;
    }

    /**
     * @param        $skus
     * @param string $type
     * @param null   $segmentId
     * @param null   $filter
     * @param bool   $attributes
     * @param string $attributeCode
     *
     * @return array
     */
    public function getVennData(
        $skus,
        $type = 'global',
        $segmentId = null,
        $filter = null,
        $attributes = false,
        $attributeCode = ''
    ) {

        if (!$type) {
            $type = 'global';
        }

        $maxColumns = $this->connection->fetchOne(
            $this->connection->select()
                             ->from($this->getTable('sales_order'), ['MAX(total_item_count)'])
        );

        if ($maxColumns > self::NUMBER_PRODUCTS_RELATED) {
            $maxColumns = self::NUMBER_PRODUCTS_RELATED;
        }

        $vennHistoryTable = $this->getTable('panda_products_venn_history');

        $row = [];
        if (count($skus) == 1) {
            $tableName = $this->getTable(self::PRODUCTS_RELATIONS_TABLE_PREFIX . $type);

            if ($attributes) {
                $tableName = $this->getTable(self::PRODUCTS_RELATIONS_TABLE_PREFIX_ATTRS . $type);
            }

            $sku = reset($skus);
            $row[$sku] = $this->connection->fetchRow(
                $this->connection->select()
                                 ->from($tableName)
                                 ->where('sku=?', (string) $sku)
                                 ->where('segment_id IS NULL')
            );

            if ($row[$sku]) {
                for ($i = 1; $i <= 4; $i++) {
                    if ($row[$sku]['related_' . $i]) {
                        $select = $this->connection->select()
                                                   ->from($tableName)
                                                   ->where('sku=?', (string) $row[$sku]['related_' . $i])
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

        foreach (range(1, $maxColumns) as $number) {
            $fields[] = 'sku_' . $number;
        }

        $startMySQL = $this->pandaHelper->gmtDate();
        $exists = $this->connection->fetchRow(
            $this->connection->select()
                             ->from($vennHistoryTable, ['data', 'item_id'])
                             ->where('identifier=?', $identifier)
                             ->where('updated_at >=  ? - INTERVAL 1 DAY ', $startMySQL)
        );

        if ($exists) {
            $this->connection->update(
                $vennHistoryTable,
                ['views' => new \Zend_Db_Expr('views + 1 ')],
                ['item_id=?' => $exists['item_id']]
            );

            /** @todo COMMENT/UNCOMMENT */
            return json_decode($exists['data'], true);
        }

        $collect = $this->createSkuCombination($final);

        $values = [];

        $auxFields = ['age', 'countries', 'regions'];
        $types = self::REPORT_TYPES;
        unset($types['regions'], $types['countries']);

        if (in_array($type, ['countries', 'regions'])) {
            $types = [$type];
        }

        foreach ($types as $sType) {
            if ($attributes) {
                $tableVennName = $this->getTable(self::PRODUCTS_VENN_TABLE_PREFIX_ATTRS . $sType);
            } else {
                $tableVennName = $this->getTable(self::PRODUCTS_VENN_TABLE_PREFIX . $sType);
            }

            if (in_array($sType, $auxFields) && !$filter) {
                $subTypes = $this->getFieldRangeVenn($sType, $sType, $attributes, $attributeCode);
            } else {
                $subTypes = [0];
            }

            $order = '';
            if ($sType == 'regions') {
                $order = 'region';
            } elseif ($sType == 'countries') {
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

                    if (in_array($sType, $auxFields) && $age !== 0) {
                        if ($sType == 'regions') {
                            $fieldSelect = 'region';
                        } elseif ($sType == 'countries') {
                            $fieldSelect = 'country';
                        } else {
                            $fieldSelect = $sType;
                        }

                        $select->where($fieldSelect . '=?', $age);
                    }

                    if ($segmentId) {
                        $select->where('segment_id =?', $segmentId);
                    }

                    if ($filter) {
                        foreach ($filter as $key => $value) {
                            $select->where($key, $value);
                        }
                    }

                    if ($attributes) {
                        $select->where('attribute_code=?', $attributeCode);
                    }

                    $total = $this->connection->fetchOne($select);

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
     * @param string $type
     * @param string $field
     * @param bool   $segmentId
     *
     * @param bool   $attributes
     *
     * @return array
     */
    public function getPossibleVennOptions(
        $type = 'country',
        $field = 'country',
        $segmentId = false,
        $attributes = false
    ) {

        $table = $this->getTable(self::PRODUCTS_VENN_TABLE_PREFIX . $type);
        if ($attributes) {
            $table = $this->getTable(self::PRODUCTS_VENN_TABLE_PREFIX_ATTRS . $type);
        }

        $select = $this->connection->select()
                                   ->from(
                                       $table,
                                       ["DISTINCT({$this->connection->quoteIdentifier($field)})"]
                                   );

        if ($segmentId) {
            $select->where('segment_id=?', $segmentId);
        }

        $select->order($field);

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

        array_multisort(array_map('count', $results), SORT_ASC, $results);

        return $results;
    }

    /**
     * @param        $sku
     * @param string $type
     * @param string $field
     * @param bool   $attributes
     *
     * @return array
     */
    public function getFieldRange($sku, $type = 'global', $field = 'age', $attributes = false)
    {

        if ($field == 'countries') {
            $field = 'country';
        }
        if ($field == 'regions') {
            $field = 'region';
        }

        $tableName = $this->getTable(self::PRODUCTS_RELATIONS_TABLE_PREFIX . $type);

        if ($attributes) {
            $tableName = $this->getTable(self::PRODUCTS_RELATIONS_TABLE_PREFIX_ATTRS . $type);
        }

        $days = $this->connection->fetchCol(
            $this->connection->select()
                             ->from($tableName, [])
                             ->columns(
                                 [
                                     'distinct' => new \Zend_Db_Expr("DISTINCT($field)"),
                                 ]
                             )
                             ->where('sku = ?', $sku)
                             ->order($field)
        );

        return $days;
    }

    /**
     * @param string $type
     * @param string $field
     * @param bool   $attributes
     * @param string $attributeCode
     *
     * @return array
     */
    public function getFieldRangeVenn($type = 'global', $field = 'age', $attributes = false, $attributeCode = '')
    {

        if ($field == 'countries') {
            $field = 'country';
        }
        if ($field == 'regions') {
            $field = 'region';
        }

        $tableName = $this->getTable(self::PRODUCTS_RELATIONS_TABLE_PREFIX . $type);

        if ($attributes) {
            $tableName = $this->getTable(self::PRODUCTS_RELATIONS_TABLE_PREFIX_ATTRS . $type);
        }

        $fieldIdent = $this->connection->quoteIdentifier($field);
        $select = $this->connection->select()
                                   ->from($tableName, [])
                                   ->columns(
                                       [
                                           'distinct' => new \Zend_Db_Expr("DISTINCT($fieldIdent)"),
                                       ]
                                   )
                                   ->order($field);

        if ($attributes) {
            $select->where('attribute_code=?', $attributeCode);
        }

        $days = $this->connection->fetchCol($select);

        return $days;
    }

    /**
     * @return array
     */
    public function getTypes()
    {

        $types = $this->salesStats->create()->getTypes();

        if (isset($types['gender'])) {
            unset($types['gender']);

            $types['male'] = 'male';
            $types['female'] = 'female';
        }

        unset($types['attribute']);

        return array_keys($types);
    }

    /**
     * @return Relations
     */
    public function reindexVenn()
    {

        return $this->rebuildVennAll();
    }

    /**
     * @param bool $date
     *
     * @return $this
     */
    public function rebuildVennAll($date = false)
    {

        $types = $this->getTypes();

        $segments = $this->segmentsFactory->create()
                                          ->getCollection()
                                          ->addFieldToFilter('products_relations', 1);

        if (!$this->getData('consoleOutput') && !$this->indexer->canReindex('venn')) {
            throw new \RuntimeException("Indexer status does not allow reindexing");
        }

        if (!$date) {
            $types = $this->indexer->getTypesToReindex($types, 'venn');
            $this->indexer->updateIndexStatus(Indexer::STATUS_WORKING, 'venn');
        }

        foreach ($types as $type) {
            $this->rebuildVenn($date, false, $type);
            $this->rebuildVenn($date, false, $type, true);

            /** @var \Licentia\Equity\Model\Segments $segment */
            foreach ($segments as $segment) {
                $this->rebuildVenn($date, $segment->getId(), $type);
                $this->rebuildVenn($date, $segment->getId(), $type, true);
            }

            if (!$date) {
                $this->indexer->updateIndex($type, 0, 'venn');
            }
        }

        if (!$date) {
            $this->indexer->updateIndexStatus(Indexer::STATUS_VALID, 'venn');
        }

        return $this;
    }

    /**
     * @param bool   $date
     * @param bool   $segmentId
     * @param string $type
     * @param bool   $attributes
     *
     */
    public function rebuildVenn($date = false, $segmentId = false, $type = 'global', $attributes = false)
    {

        $table = self::PRODUCTS_VENN_TABLE_PREFIX . $type;
        if ($attributes) {
            $table = self::PRODUCTS_VENN_TABLE_PREFIX_ATTRS . $type;
        }

        $mainTable = $this->getTable($table);
        $invoiceItemTable = $this->getTable('sales_invoice_item');
        $salesInvoiceTable = $this->getTable('sales_invoice');
        $salesTable = $this->getTable('sales_order');

        if (!$date && !$segmentId) {
            $this->connection->delete($mainTable, ['segment_id IS NULL']);
        }

        if ($segmentId && !$date) {
            $this->connection->delete($mainTable, ['segment_id=?' => $segmentId]);
        }

        $attributesLoop = [0];
        if ($attributes) {
            $attributesConfig = $this->scopeConfig->getValue(
                'panda_equity/reports/attributes',
                ScopeInterface::SCOPE_WEBSITE
            );

            if (!$attributesConfig) {
                $attributesLoop = [];
            } else {
                $attributesLoop = explode(',', $attributesConfig);
            }
        }

        foreach ($attributesLoop as $attributeCode) {
            $select = $this->connection->select();
            $select->reset()
                   ->from(['a' => $invoiceItemTable], ['total' => 'COUNT(*)'])
                   ->joinInner(['si' => $salesInvoiceTable], 'a.parent_id = si.entity_id', [])
                   ->joinInner(['s' => $salesTable], 's.entity_id = si.order_id ', []);

            if ($type == 'country') {
                $select->joinInner(
                    ['ad' => $this->getTable('sales_order_address')],
                    "s.entity_id = ad.parent_id AND ad.address_type = 'billing' ",
                    ['country' => 'country_id']
                );

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
                    $select->where(' ad.country_id IN (?)', $countries);
                }

                $select->group('country');
            }

            if ($type == 'region') {
                $regionsTable = $this->getTable('directory_country_region_name');
                $select->where("ad.region_id IN (SELECT DISTINCT(region_id) FROM $regionsTable)")
                       ->joinInner(
                           ['ad' => $this->getTable('sales_order_address')],
                           "s.entity_id = ad.parent_id AND ad.address_type = 'billing' AND LENGTH(TRIM(region)) > 1 ",
                           ['region' => "CONCAT(TRIM(region), ' - ', country_id)"]
                       );

                $select->group('TRIM(region)');

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
                    $select->where(' ad.region_id IN (?)', $regions);
                }
            }

            if ($type == 'female' || $type == 'male') {
                $select->joinInner(
                    ['k' => $this->getTable('panda_customers_kpis')],
                    "s.customer_email = k.email_meta AND (gender='$type' OR (gender IS NULL and predicted_gender='$type'))",
                    []
                );
            }

            if ($type == 'age') {
                $select->joinInner(
                    ['k' => $this->getTable('panda_customers_kpis')],
                    's.customer_email = k.email_meta AND (age>18 OR predicted_age IS NOT NULL)',
                    []
                );
                $newColumns = [
                    'age' => new \Zend_Db_Expr(self::SQL_AGE_EXPRESSION),
                ];

                $select->columns($newColumns);

                $select->group('age');
            }

            if ($segmentId) {
                $select->joinInner(
                    ['p' => $this->getTable('panda_segments_records')],
                    's.customer_email = p.email',
                    []
                );
            }

            if ($date) {
                $select->where('a.created_at =?', $date);
            }

            if ($segmentId) {
                $select->where('p.segment_id=?', $segmentId);
            }

            $select->group('a.parent_id');

            if ($attributes) {
                $select->join(['e' => $this->getTable('catalog_product_entity')], "e.sku = a.sku", []);
                $col = $this->productFactory->create()
                                            ->getCollection()->addAttributeToFilter($attributeCode, ['neq' => 100]);

                $joinsAttributes = $col->getSelect()->getPart('from');
                unset($joinsAttributes['e']);

                $keyName = key($joinsAttributes);

                $select->where($keyName . '.value IS NOT NULL');

                $select->join(
                    [$keyName => $joinsAttributes[$keyName]['tableName']],
                    $joinsAttributes[$keyName]['joinCondition'],
                    []
                );

                $columns = $select->getPart('columns');

                foreach ($columns as $key => $column) {
                    if (isset($column[2]) && $column[2] == 'sku') {
                        unset($columns[$key]);
                    }
                }

                $select->columns(['sku' => new \Zend_Db_Expr("GROUP_CONCAT(DISTINCT($keyName.value))")]);
            } else {
                $select->columns(['sku' => 'GROUP_CONCAT(a.sku)']);
            }

            $result = $this->connection->fetchAll($select);

            if (!$result) {
                continue;
            }

            foreach ($result as $item) {
                $data = [];

                if ($segmentId) {
                    $data['segment_id'] = $segmentId;
                }
                if ($attributes) {
                    $data['attribute_code'] = $attributeCode;
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
                $info['sku'] = str_getcsv($item['sku']);

                sort($info['sku']);

                $i = 1;
                foreach ($info['sku'] as $value) {
                    if ($i > 50) {
                        break;
                    }

                    $data['sku_' . $i] = $value;
                    $i++;
                }

                $this->connection->insert($mainTable, $data);
            }
        }

        if ($output = $this->getData('consoleOutput')) {
            if ($output instanceof OutputInterface) {
                $extra = '';
                if ($segmentId) {
                    $extra = ' / SEGID: ' . $segmentId;
                }

                $extra .= ' - ' . date('Y-m-d H:i:s');

                $output->writeln("Ven | Finished: " . $mainTable . $extra);
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
     * @param $sku
     *
     * @return string
     */
    public function getProductName($sku = null)
    {

        if (!$sku) {
            return '';
        }

        $sql = "SELECT 	v.`value` 
                FROM 	{$this->getTable('catalog_product_entity')} c
                    JOIN {$this->getTable('catalog_product_entity_varchar')} v ON c.entity_id = v.entity_id
                    JOIN {$this->getTable('eav_attribute')} a ON v.attribute_id = a.attribute_id
                    JOIN {$this->getTable('eav_entity_type')} t ON a.entity_type_id = t.entity_type_id
                WHERE 
                    c.sku = ?
                AND a.attribute_code = 'name' 
                AND t.entity_type_code = 'catalog_product'";

        return $this->connection->fetchOne($sql, (string) $sku);
    }

    /**
     * @param $attributeId
     *
     * @return string
     */
    public function getAttributeName($attributeId)
    {

        $connection = $this->productFactory->create()->getResource()->getConnection();

        $select = $connection->select()
                             ->from(
                                 $this->productFactory->create()
                                                      ->getResource()
                                                      ->getTable('eav_attribute_option_value'),
                                 ['value']
                             )
                             ->where('option_id=?', $attributeId)
                             ->where('store_id=?', 0);

        return $connection->fetchOne($select);
    }
}
