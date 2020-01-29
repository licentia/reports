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
    protected $searchResource;

    /**
     * @var Indexer
     */
    protected $indexer;

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
        \Licentia\Reports\Model\Indexer $indexer,
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
    protected $_eventPrefix = 'panda_search_grid';

    /**
     *
     */
    protected function _construct()
    {

        $this->_init(\Licentia\Reports\Model\ResourceModel\Search::class);
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
