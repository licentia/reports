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
 * @modified   03/06/20, 16:19 GMT
 *
 */

namespace Licentia\Reports\Cron;

/**
 * Class RebuildSalesStats
 *
 * @package Licentia\Panda\Cron
 */
class RebuildSalesStatsForYesterday
{

    /**
     * @var \Licentia\Panda\Helper\Data
     */
    protected $pandaHelper;

    /**
     * @var \Licentia\Reports\Model\Sales\StatsFactory
     */
    protected $statsFactory;

    /**
     * @var \Licentia\Reports\Model\Sales\OrdersFactory
     */
    protected $ordersFactory;

    /**
     * RebuildSalesStatsForYesterday constructor.
     *
     * @param \Licentia\Reports\Model\Sales\StatsFactory  $statsFactory
     * @param \Licentia\Reports\Model\Sales\OrdersFactory $ordersFactory
     * @param \Licentia\Panda\Helper\Data                 $pandaHelper
     */
    public function __construct(
        \Licentia\Reports\Model\Sales\StatsFactory $statsFactory,
        \Licentia\Reports\Model\Sales\OrdersFactory $ordersFactory,
        \Licentia\Panda\Helper\Data $pandaHelper
    ) {

        $this->statsFactory = $statsFactory;
        $this->ordersFactory = $ordersFactory;
        $this->pandaHelper = $pandaHelper;
    }

    /**
     * @return $this
     */
    public function execute()
    {

        try {
            $this->statsFactory->create()->rebuildForYesterday();
            $this->ordersFactory->create()->rebuildForYesterday();
        } catch (\Exception $e) {
            $this->pandaHelper->logWarning($e);
        }

        return $this;
    }
}
