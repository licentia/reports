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
    protected \Licentia\Panda\Helper\Data $pandaHelper;

    /**
     * @var \Licentia\Reports\Model\Sales\StatsFactory
     */
    protected \Licentia\Reports\Model\Sales\StatsFactory $statsFactory;

    /**
     * @var \Licentia\Reports\Model\Sales\OrdersFactory
     */
    protected \Licentia\Reports\Model\Sales\OrdersFactory $ordersFactory;

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
