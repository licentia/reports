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

namespace Licentia\Reports\Model;

/**
 * Class Indexer
 *
 * @package Licentia\Panda\Model
 */
class Indexer extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var array
     */
    const AVAILABLE_INDEXES = [
        'equity'             => 'equity',
        'reorders'           => 'reorders',
        'performance'        => 'performance',
        'relations'          => 'relations',
        'venn'               => 'venn',
        'recommendations'    => 'recommendations',
        'sales'              => 'sales',
        'segments'           => 'segments',
        'search_performance' => 'search_performance',
        'search_history'     => 'search_history',
    ];

    /**
     * @var array
     */
    const INDEXES_PRIORITY = [
        'equity'             => 10,
        'reorders'           => 20,
        'performance'        => 30,
        'relations'          => 40,
        'venn'               => 50,
        'recommendations'    => 60,
        'sales'              => 70,
        'segments'           => 80,
        'search_performance' => 90,
        'search_history'     => 100,
    ];

    /**
     *
     */
    const STATUS_WORKING = 'working';

    /**
     *
     */
    const STATUS_TYPE = 'type';

    /**
     *
     */
    const STATUS_VALID = 'valid';

    /**
     *
     */
    const STATUS_INVALID = 'invalid';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'panda_indexer_state';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'panda_indexer_state';

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected $pandaHelper;

    /**
     * @var array
     */
    private $indexers = [];

    /**
     * Indexer constructor.
     *
     * @param \Licentia\Equity\Model\MetadataFactory                       $equity
     * @param \Licentia\Reports\Model\Sales\StatsFactory                   $performanceFactory
     * @param \Licentia\Reports\Model\Products\RelationsFactory            $relationsFactory
     * @param \Licentia\Reports\Model\Search\StatsFactory                  $searchFactory
     * @param SearchFactory                                                $searchHistoryFactory
     * @param \Licentia\Equity\Model\SegmentsFactory                       $segmentsFactory
     * @param \Licentia\Reports\Model\Sales\OrdersFactory                  $ordersFactory
     * @param \Licentia\Reports\Model\Sales\ExpectedReOrdersFactory        $expectedReOrdersFactory
     * @param \Licentia\Reports\Helper\Data                                $pandaHelper
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Licentia\Equity\Model\MetadataFactory $equity,
        \Licentia\Reports\Model\Sales\StatsFactory $performanceFactory,
        \Licentia\Reports\Model\Products\RelationsFactory $relationsFactory,
        \Licentia\Reports\Model\Search\StatsFactory $searchFactory,
        SearchFactory $searchHistoryFactory,
        \Licentia\Equity\Model\SegmentsFactory $segmentsFactory,
        \Licentia\Reports\Model\Sales\OrdersFactory $ordersFactory,
        \Licentia\Reports\Model\Sales\ExpectedReOrdersFactory $expectedReOrdersFactory,
        \Licentia\Reports\Helper\Data $pandaHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->pandaHelper = $pandaHelper;
        $this->connection = $this->getResource()->getConnection();

        $this->indexers['equity'] = $equity;
        $this->indexers['reorders'] = $expectedReOrdersFactory;
        $this->indexers['performance'] = $performanceFactory;
        $this->indexers['relations'] = $relationsFactory;
        $this->indexers['venn'] = $relationsFactory;
        $this->indexers['recommendations'] = $relationsFactory;
        $this->indexers['sales'] = $ordersFactory;
        $this->indexers['segments'] = $segmentsFactory;
        $this->indexers['search_performance'] = $searchFactory;
        $this->indexers['search_history'] = $searchHistoryFactory;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_init(ResourceModel\Indexer::class);
    }

    /**
     * @return $this
     */
    public function checkIndexers()
    {

        foreach ($this->indexers as $key => $indexer) {
            $this->load($key);
        }

        return $this;
    }

    /**
     * @param int  $modelId
     * @param null $field
     *
     * @return $this|Indexer|\Magento\Framework\Model\AbstractModel
     */
    public function load($modelId, $field = null)
    {

        if (!in_array($modelId, self::AVAILABLE_INDEXES)) {
            throw new \InvalidArgumentException("{$modelId} indexer does not exist.");
        }

        $indexer = parent::load($modelId);

        if (!$indexer->getId() || $indexer->getId() != $modelId) {
            $this->connection->insert(
                $this->getResource()->getMainTable(),
                [
                    'indexer_id' => $modelId,
                    'priority'   => self::INDEXES_PRIORITY[$modelId],
                ]
            );

            return parent::load($modelId);
        }

        return $this;
    }

    /**
     * @return Indexer
     */
    public function reindex()
    {

        return $this->setStatus(self::STATUS_INVALID)
                    ->setUpdatedAt(null)
                    ->setLastEntityId(null)
                    ->setLastEntityIdUpdatedAt(null)
                    ->save();
    }

    /**
     *
     */
    public function reindexInvalidated()
    {

        $date = new \DateTime($this->pandaHelper->gmtDate());
        $date->sub(new \DateInterval('PT12H'));
        $dateOld = $date->format('Y-m-d H:i:s');

        $collection = $this->getCollection()->setOrder('priority');

        $collection->getSelect()
                   ->where('status = ?', self::STATUS_INVALID)
                   ->orWhere("status ='" . self::STATUS_WORKING . "' AND updated_at<=? ", $dateOld);

        /** @var self $reindex */
        foreach ($collection as $reindex) {
            $name = "reindex" . ucfirst(str_replace('_', '', $reindex->getIndexerId()));
            $this->indexers[$reindex->getIndexerId()]->create()->$name();
        }
    }

    /**
     *
     */
    public function reindexAll()
    {

        foreach (self::AVAILABLE_INDEXES as $index) {
            $this->load($index);
        }

        $collection = $this->getCollection()->setOrder('priority');

        /** @var self $reindex */
        foreach ($collection as $reindex) {
            $name = "reindex" . ucfirst($reindex->getIndexerId());
            $this->indexers[$reindex->getIndexerId()]->create()->$name();
        }
    }

    /**
     * @param $indexer
     *
     * @return bool
     */
    public function canReindex($indexer)
    {

        $indexer = $this->load($indexer);

        return ($indexer->getStatus() != self::STATUS_WORKING);
    }

    /**
     * @return $this|\Magento\Framework\Model\AbstractModel
     */
    public function delete()
    {

        return $this;
    }

    /**
     * @param $status
     * @param $indexer
     *
     * @return Indexer
     */
    public function updateIndexStatus($status, $indexer)
    {

        $indexer = $this->load($indexer)
                        ->setUpdatedAt($this->pandaHelper->gmtDate())
                        ->setStatus($status)
                        ->setCycle(0);

        if ($status == self::STATUS_VALID) {
            $indexer->setLastEntityId(null)
                    ->setEntityType(null)
                    ->setLastEntityIdUpdatedAt(null);
        }

        return $indexer->save();
    }

    /**
     * @param $types
     * @param $indexer
     *
     * @return array
     */
    public function getTypesToReindex($types, $indexer)
    {

        $this->load($indexer);

        if ($this->getCycle() == 1) {
            return array_slice($types, array_search($this->getEntityType(), array_values($types)));
        }

        return $types;
    }

    /**
     * @param $entityType
     * @param $entityId
     * @param $indexer
     *
     * @return Indexer
     */
    public function updateIndex($entityType, $entityId, $indexer)
    {

        return $this->load($indexer)
                    ->setEntityType($entityType)
                    ->setCycle(1)
                    ->setLastEntityId($entityId)
                    ->setLastEntityIdUpdatedAt($this->pandaHelper->gmtDate())
                    ->save();
    }

    /**
     * @param $stateId
     *
     * @return $this
     */
    public function setStateId($stateId)
    {

        return $this->setData('state_id', $stateId);
    }

    /**
     * @param $indexerId
     *
     * @return $this
     */
    public function setIndexerId($indexerId)
    {

        return $this->setData('indexer_id', $indexerId);
    }

    /**
     * @param $status
     *
     * @return $this
     */
    public function setStatus($status)
    {

        return $this->setData('status', $status);
    }

    /**
     * @param $cycle
     *
     * @return $this
     */
    public function setCycle($cycle)
    {

        return $this->setData('cycle', $cycle);
    }

    /**
     * @param $entityType
     *
     * @return $this
     */
    public function setEntityType($entityType)
    {

        return $this->setData('entity_type', $entityType);
    }

    /**
     * @param $lastEntityId
     *
     * @return $this
     */
    public function setLastEntityId($lastEntityId)
    {

        return $this->setData('last_entity_id', $lastEntityId);
    }

    /**
     * @param $lastEntityIdUpdatedAt
     *
     * @return $this
     */
    public function setLastEntityIdUpdatedAt($lastEntityIdUpdatedAt)
    {

        return $this->setData('last_entity_id_updated_at', $lastEntityIdUpdatedAt);
    }

    /**
     * @param $updated
     *
     * @return $this
     */
    public function setUpdatedAt($updated)
    {

        return $this->setData('updated_at', $updated);
    }

    /**
     * @return mixed
     */
    public function getStateId()
    {

        return $this->getData('state_id');
    }

    /**
     * @return mixed
     */
    public function getIndexerId()
    {

        return $this->getData('indexer_id');
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {

        return $this->getData('status');
    }

    /**
     * @return mixed
     */
    public function getCycle()
    {

        return $this->getData('cycle');
    }

    /**
     * @return mixed
     */
    public function getEntityType()
    {

        return $this->getData('entity_type');
    }

    /**
     * @return mixed
     */
    public function getLastEntityId()
    {

        return $this->getData('last_entity_id');
    }

    /**
     * @return mixed
     */
    public function getLastEntityIdUpdatedAt()
    {

        return $this->getData('last_entity_id_updated_at');
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {

        return $this->getData('updated_at');
    }
}
