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
 * @modified   17/03/20, 20:41 GMT
 *
 */

namespace Licentia\Reports\Cron;

/**
 * Class RebuildSearchPerformance
 *
 * @package Licentia\Reports\Cron
 */
class RebuildSearchPerformance
{

    /**
     * @var \Licentia\Reports\Logger\Logger
     */
    protected $pandaLogger;

    /**
     * @var \Licentia\Reports\Model\SearchFactory
     */
    protected $searchStats;

    /**
     * RebuildSearchPerformance constructor.
     *
     * @param \Licentia\Reports\Model\Search\StatsFactory $searchFactory
     * @param \Licentia\Reports\Logger\Logger             $pandaLogger
     */
    public function __construct(
        \Licentia\Reports\Model\Search\StatsFactory $searchFactory,
        \Licentia\Reports\Logger\Logger $pandaLogger
    ) {

        $this->pandaLogger = $pandaLogger;
        $this->searchStats = $searchFactory;
    }

    /**
     * @return bool
     */
    public function execute()
    {

        try {
            $this->searchStats->create()->reindexSearchperformance();
        } catch (\Exception $e) {
            $this->pandaLogger->warning($e->getMessage());
        }

        return true;
    }
}