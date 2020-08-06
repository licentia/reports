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

namespace Licentia\Reports\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SearchHistory
 *
 * @package Licentia\Panda\Console\Command
 */
class SearchPerformance extends Command
{

    /**
     * @var \Licentia\Reports\Model\SearchFactory
     */
    protected $searchStats;

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected $pandaHelper;

    /**
     * SearchPerformance constructor.
     *
     * @param \Licentia\Reports\Helper\Data               $pandaHelper
     * @param \Licentia\Reports\Model\Search\StatsFactory $searchFactory
     */
    public function __construct(
        \Licentia\Reports\Helper\Data $pandaHelper,
        \Licentia\Reports\Model\Search\StatsFactory $searchFactory
    ) {

        parent::__construct();
        $this->pandaHelper = $pandaHelper;
        $this->searchStats = $searchFactory;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {

        $this->setName('panda:searchperformance:rebuild')
             ->setDescription('Rebuilds Search History Grid');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $start = date_create($this->pandaHelper->gmtDate());
        $output->writeln("SearchPerformance | ");
        $output->writeln("SearchPerformance | STARTED: " . $this->pandaHelper->gmtDate());

        try {
            $this->searchStats->create()
                              ->setData('consoleOutput', $output)
                              ->reindexSearchperformance();
        } catch (\Exception $e) {
            $output->writeln("<error>SearchHistory | " . $e->getMessage() . '</error>');
        }

        $end = date_create($this->pandaHelper->gmtDate());
        $diff = date_diff($end, $start);

        $output->writeln("SearchPerformance | FINISHED: " . $this->pandaHelper->gmtDate());
        $output->writeln("SearchPerformance | The process took " . $diff->format('%h Hours %i Minutes and %s Seconds'));
        $output->writeln("SearchPerformance | ");
    }
}
