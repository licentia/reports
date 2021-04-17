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

namespace Licentia\Reports\Model;

/**
 * Class Items
 *
 * @package Licentia\Panda\Model
 */

/**
 * Class Search
 *
 * @package Licentia\Panda\Model
 */
class Search extends \Magento\Framework\Model\AbstractModel
{

    const INDEXER_NAME = 'search_history';

    /**
     * @var ResourceModel\SearchFactory
     */
    protected ResourceModel\SearchFactory $searchResource;

    /**
     * @var Indexer
     */
    protected Indexer $indexer;

    /**
     * Search constructor.
     *
     * @param Indexer                                                      $indexer
     * @param ResourceModel\SearchFactory                                  $searchResource
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        Indexer $indexer,
        \Licentia\Reports\Model\ResourceModel\SearchFactory $searchResource,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->indexer = $indexer;
        $this->searchResource = $searchResource;
    }

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected string $_eventPrefix = 'panda_search_grid';

    /**
     *
     */
    protected function _construct()
    {

        $this->_init(ResourceModel\Search::class);
    }

    /**
     *
     */
    public function reindexSearchhistory()
    {

        $this->indexer->updateIndexStatus(Indexer::STATUS_WORKING, self::INDEXER_NAME);

        $this->searchResource->create()->buildSearchGrid();

        $this->indexer->updateIndexStatus(Indexer::STATUS_VALID, self::INDEXER_NAME);

        return $this;
    }

    /**
     * @param string $term
     * @param        $sort
     * @param string $order
     *
     * @return array
     */
    public function getSearchArray($term = '', $sort = 'today', $order = 'DESC')
    {

        $resource = $this->searchResource->create();

        $connection = $resource->getConnection();

        $result = $connection->select()
                             ->from(
                                 $this->getResource()
                                      ->getTable('panda_search_grid')
                             )
                             ->order($sort . ' ' . $order);
        if ($term) {
            $result->where('term LIKE ?', '%' . $term . '%');
        }

        return $connection->fetchAll($result);
    }

    /**
     * @param string $term
     *
     * @return array
     */
    public function getMetadataSearchArray($term = '')
    {

        $resource = $this->searchResource->create();

        $connection = $resource->getConnection();

        $result = $connection->select()
                             ->from(
                                 $resource->getTable('panda_segments_metadata_searches'),
                                 ['query', 'query']
                             )
                             ->limit(20)
                             ->group('query')
                             ->order('query ASC ');

        if ($term) {
            $result->where('query LIKE ?', '%' . $term . '%');
        }

        return $connection->fetchPairs($result);
    }
}
