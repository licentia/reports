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
 * Class RebuildSearchPerformance
 *
 * @package Licentia\Reports\Cron
 */
class RebuildSearchPerformance
{

    /**
     * @var \Licentia\Panda\Helper\Data
     */
    protected \Licentia\Panda\Helper\Data $pandaHelper;

    /**
     * @var \Licentia\Reports\Model\SearchFactory
     */
    protected $searchStats;

    /**
     * RebuildSearchPerformance constructor.
     *
     * @param \Licentia\Reports\Model\Search\StatsFactory $searchFactory
     * @param \Licentia\Panda\Helper\Data                 $pandaHelper
     */
    public function __construct(
        \Licentia\Reports\Model\Search\StatsFactory $searchFactory,
        \Licentia\Panda\Helper\Data $pandaHelper
    ) {

        $this->pandaHelper = $pandaHelper;
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
            $this->pandaHelper->logWarning($e);
        }

        return true;
    }
}
