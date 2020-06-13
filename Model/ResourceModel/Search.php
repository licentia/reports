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

namespace Licentia\Reports\Model\ResourceModel;

use Licentia\Reports\Model\Indexer;

/**
 * Class Items
 *
 * @package Licentia\Panda\Model\ResourceModel
 */
class Search extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var \Licentia\Reports\Model\IndexerFactory
     */
    protected $indexer;

    /**
     * Search constructor.
     *
     * @param \Licentia\Reports\Model\IndexerFactory            $indexer
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param null                                              $connectionName
     */
    public function __construct(
        \Licentia\Reports\Model\IndexerFactory $indexer,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {

        parent::__construct($context, $connectionName);

        $this->indexer = $indexer->create();
    }

    /**
     * @var string
     */
    protected $_idFieldName = 'item_id';

    /**
     * Initialize resource model
     * Get tablename from config
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_init('panda_search_grid', 'item_id');
    }

    /**
     *
     */
    public function buildSearchGrid()
    {

        if (!$this->indexer->canReindex('search_history')) {
            throw new \RuntimeException("Indexer status does not allow reindexing");
        }

        $this->indexer->updateIndexStatus(Indexer::STATUS_WORKING, 'search_history');

        $searchTable = $this->getTable('panda_search_grid');

        $searchHistoryTable = $this->getTable('panda_search_history');
        $this->getConnection()->truncateTable($searchTable);

        $query = "INSERT IGNORE INTO $searchTable (
                        `term`,
                        `results`,
                        `today`,
                        `today_1`,
                        `today_2`,
                        `today_3`,
                        `today_4`,
                        `today_5`,
                        `today_6`,
                        `last_7days`,
                        `last_714days`,
                        `last_1421days`,
                        `last_2128days`,
                        `last_30days`,
                        `last_3060days`,
                        `last_365days`,
                        `total` 
                    ) SELECT
                    TRIM(
                        REPLACE (
                            REPLACE (
                                REPLACE ( $searchHistoryTable.`query`, '\t', '' ),
                                '\n',
                                '' 
                            ),
                            '\r',
                            '‌​' 
                        ) 
                    ) AS `Query`,
                    $searchHistoryTable.`results` AS `results`,
                    (
                        SELECT
                            count( * ) 
                        FROM
                            $searchHistoryTable AS st 
                        WHERE
                            st.`query` = $searchHistoryTable.`query` 
                            AND created_at >= DATE_FORMAT( NOW( ), '%Y-%m-%d 00:00:00' ) 
                        GROUP BY
                            $searchHistoryTable.`query` 
                        ) AS `Today`,
                        (
                        SELECT
                            count( * ) 
                        FROM
                            $searchHistoryTable AS st 
                        WHERE
                            st.`query` = $searchHistoryTable.`query` 
                            AND DATE_FORMAT( created_at, '%Y-%m-%d' ) = DATE_SUB(
                                DATE_FORMAT( NOW( ), '%Y-%m-%d' ),
                                INTERVAL 1 DAY 
                            ) 
                        GROUP BY
                            $searchHistoryTable.`query` 
                        ) AS `Today -1`,
                        (
                        SELECT
                            count( * ) 
                        FROM
                            $searchHistoryTable AS st 
                        WHERE
                            st.`query` = $searchHistoryTable.`query` 
                            AND DATE_FORMAT( created_at, '%Y-%m-%d' ) = DATE_SUB(
                                DATE_FORMAT( NOW( ), '%Y-%m-%d' ),
                                INTERVAL 2 DAY 
                            ) 
                        GROUP BY
                            $searchHistoryTable.`query` 
                        ) AS `Today -2`,
                        (
                        SELECT
                            count( * ) 
                        FROM
                            $searchHistoryTable AS st 
                        WHERE
                            st.`query` = $searchHistoryTable.`query` 
                            AND DATE_FORMAT( created_at, '%Y-%m-%d' ) = DATE_SUB(
                                DATE_FORMAT( NOW( ), '%Y-%m-%d' ),
                                INTERVAL 3 DAY 
                            ) 
                        GROUP BY
                            $searchHistoryTable.`query` 
                        ) AS `Today -3`,
                        (
                        SELECT
                            count( * ) 
                        FROM
                            $searchHistoryTable AS st 
                        WHERE
                            st.`query` = $searchHistoryTable.`query` 
                            AND DATE_FORMAT( created_at, '%Y-%m-%d' ) = DATE_SUB(
                                DATE_FORMAT( NOW( ), '%Y-%m-%d' ),
                                INTERVAL 4 DAY 
                            ) 
                        GROUP BY
                            $searchHistoryTable.`query` 
                        ) AS `Today -4`,
                        (
                        SELECT
                            count( * ) 
                        FROM
                            $searchHistoryTable AS st 
                        WHERE
                            st.`query` = $searchHistoryTable.`query` 
                            AND DATE_FORMAT( created_at, '%Y-%m-%d' ) = DATE_SUB(
                                DATE_FORMAT( NOW( ), '%Y-%m-%d' ),
                                INTERVAL 5 DAY 
                            ) 
                        GROUP BY
                            $searchHistoryTable.`query` 
                        ) AS `Today -5`,
                        (
                        SELECT
                            count( * ) 
                        FROM
                            $searchHistoryTable AS st 
                        WHERE
                            st.`query` = $searchHistoryTable.`query` 
                            AND DATE_FORMAT( created_at, '%Y-%m-%d' ) = DATE_SUB(
                                DATE_FORMAT( NOW( ), '%Y-%m-%d' ),
                                INTERVAL 6 DAY 
                            ) 
                        GROUP BY
                            $searchHistoryTable.`query` 
                        ) AS `Today -6`,
                        (
                        SELECT
                            count( * ) 
                        FROM
                            $searchHistoryTable AS st 
                        WHERE
                            st.`query` = $searchHistoryTable.`query` 
                            AND created_at >= DATE_SUB( NOW( ), INTERVAL 6 DAY ) 
                        GROUP BY
                            $searchHistoryTable.`query` 
                            LIMIT 1 
                        ) AS `Last 7 Days`,
                        (
                        SELECT
                            count( * ) 
                        FROM
                            $searchHistoryTable AS st 
                        WHERE
                            st.`query` = $searchHistoryTable.`query` 
                            AND created_at >= DATE_SUB( NOW( ), INTERVAL 13 DAY ) 
                            AND created_at < DATE_SUB( NOW( ), INTERVAL 6 DAY ) 
                        GROUP BY
                            $searchHistoryTable.`query` 
                            LIMIT 1 
                        ) AS `Last 14-7 Days`,
                        (
                        SELECT
                            count( * ) 
                        FROM
                            $searchHistoryTable AS st 
                        WHERE
                            st.`query` = $searchHistoryTable.`query` 
                            AND created_at >= DATE_SUB( NOW( ), INTERVAL 20 DAY ) 
                            AND created_at < DATE_SUB( NOW( ), INTERVAL 13 DAY ) 
                        GROUP BY
                            $searchHistoryTable.`query` 
                            LIMIT 1 
                        ) AS `Last 21-14 Days`,
                        (
                        SELECT
                            count( * ) 
                        FROM
                            $searchHistoryTable AS st 
                        WHERE
                            st.`query` = $searchHistoryTable.`query` 
                            AND created_at >= DATE_SUB( NOW( ), INTERVAL 27 DAY ) 
                            AND created_at < DATE_SUB( NOW( ), INTERVAL 20 DAY ) 
                        GROUP BY
                            $searchHistoryTable.`query` 
                            LIMIT 1 
                        ) AS `Last 28-21 Days`,
                        (
                        SELECT
                            count( * ) 
                        FROM
                            $searchHistoryTable AS st 
                        WHERE
                            st.`query` = $searchHistoryTable.`query` 
                            AND created_at >= DATE_SUB( NOW( ), INTERVAL 30 DAY ) 
                        GROUP BY
                            $searchHistoryTable.`query` 
                        ) AS `Last 30 days`,
                        (
                        SELECT
                            count( * ) 
                        FROM
                            $searchHistoryTable AS st 
                        WHERE
                            st.`query` = $searchHistoryTable.`query` 
                            AND created_at >= DATE_SUB( NOW( ), INTERVAL 60 DAY ) 
                            AND created_at < DATE_SUB( NOW( ), INTERVAL 30 DAY ) 
                        GROUP BY
                            $searchHistoryTable.`query`,
                            DATE_FORMAT( created_at, '%Y-%m' ) 
                        ) AS `Previous 30 days`,
                        (
                        SELECT
                            count( * ) 
                        FROM
                            $searchHistoryTable AS st 
                        WHERE
                            st.`query` = $searchHistoryTable.`query` 
                            AND created_at >= DATE_SUB( NOW( ), INTERVAL 365 DAY ) 
                        GROUP BY
                            $searchHistoryTable.`query` 
                        ) AS `Current Year`,
                        (
                        SELECT
                            count( * ) 
                        FROM
                            $searchHistoryTable AS st 
                        WHERE
                            st.`query` = $searchHistoryTable.`query` 
                        GROUP BY
                            TRIM(
                                REPLACE (
                                    REPLACE (
                                        REPLACE ( $searchHistoryTable.`query`, '\t', '' ),
                                        '\n',
                                        '' 
                                    ),
                                    '\r',
                                    '‌​' 
                                ) 
                            ) 
                        ) AS `total` 
                    FROM
                        $searchHistoryTable 
                    GROUP BY
                        $searchHistoryTable.`query` 
                    ORDER BY
                        Today DESC

                    ";

        $this->getConnection()
             ->query($query);

        $this->indexer->updateIndexStatus(Indexer::STATUS_VALID, 'search_history');
    }
}
