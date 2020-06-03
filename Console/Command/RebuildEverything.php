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

namespace Licentia\Reports\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RebuildEverything
 *
 * @package Licentia\Panda\Console\Command
 */
class RebuildEverything extends Command
{

    /**
     *
     */
    const INDEXER_ARGUMENT = 'indexes';

    /**
     * @var array
     */
    protected $classes = [];

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected $pandaHelper;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * RebuildEverything constructor.
     *
     * @param \Licentia\Reports\Helper\Data                    $pandaHelper
     * @param \Licentia\Equity\Console\Command\EquityFactory   $equity
     * @param ExpectedReOrdersFactory                          $expectedReOrders
     * @param ProductsPerformanceFactory                       $performance
     * @param RecommendationsFactory                           $recommendations
     * @param RelationsFactory                                 $relations
     * @param SalesOrdersFactory                               $salesOrders
     * @param SearchHistoryFactory                             $searchHistory
     * @param SearchPerformanceFactory                         $searchPerformance
     * @param \Licentia\Equity\Console\Command\SegmentsFactory $segments
     * @param VennFactory                                      $venn
     * @param \Magento\Framework\App\State                     $appState
     */
    public function __construct(
        \Licentia\Reports\Helper\Data $pandaHelper,
        \Licentia\Equity\Console\Command\EquityFactory $equity,
        ExpectedReOrdersFactory $expectedReOrders,
        ProductsPerformanceFactory $performance,
        RecommendationsFactory $recommendations,
        RelationsFactory $relations,
        SalesOrdersFactory $salesOrders,
        SearchHistoryFactory $searchHistory,
        SearchPerformanceFactory $searchPerformance,
        \Licentia\Equity\Console\Command\SegmentsFactory $segments,
        VennFactory $venn,
        \Magento\Framework\App\State $appState
    ) {

        parent::__construct();

        $this->appState = $appState;

        $this->pandaHelper = $pandaHelper;
        $this->classes['equity'] = $equity;
        $this->classes['reorders'] = $expectedReOrders;
        $this->classes['performance'] = $performance;
        $this->classes['relations'] = $relations;
        $this->classes['venn'] = $venn;
        $this->classes['recommendations'] = $recommendations;
        $this->classes['sales'] = $salesOrders;
        $this->classes['segments'] = $segments;
        $this->classes['search_performance'] = $searchPerformance;
        $this->classes['search_history'] = $searchHistory;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {

        $this->setName('panda:rebuild')
             ->setDescription('Reindex Data')
             ->setDefinition($this->getInputList());
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        try {
            $this->appState->setAreaCode('frontend');
        } catch (\Exception $e) {
        }

        $start = date_create($this->pandaHelper->gmtDate());
        $output->writeln("RebuildAll ---------- ");
        $output->writeln("RebuildAll ---------- STARTED: " . $this->pandaHelper->gmtDate());

        foreach ($this->getIndexers($input) as $rebuild) {
            $this->classes[$rebuild]->create()->execute($input, $output);
        }

        $end = date_create($this->pandaHelper->gmtDate());
        $diff = date_diff($end, $start);

        $output->writeln("RebuildAll ---------- FINISHED: " . $this->pandaHelper->gmtDate());
        $output->writeln(
            "RebuildAll ---------- The process took " . $diff->format('%h Hours %i Minutes and %s Seconds')
        );
        $output->writeln("RebuildAll ---------- ");
    }

    /**
     * @param InputInterface $input
     *
     * @return array
     */
    protected function getIndexers(InputInterface $input)
    {

        $requestedTypes = [];
        if ($input->getArgument(self::INDEXER_ARGUMENT)) {
            $requestedTypes = $input->getArgument(self::INDEXER_ARGUMENT);
            $requestedTypes = array_filter(array_map('trim', $requestedTypes), 'strlen');
        }

        if (empty($requestedTypes)) {
            $indexers = $this->getAllIndexers();
        } else {
            $availableIndexers = $this->getAllIndexers();
            $unsupportedTypes = array_diff($requestedTypes, array_keys($availableIndexers));
            if ($unsupportedTypes) {
                throw new \InvalidArgumentException(
                    "The following requested index types are not supported: '" . join("', '", $unsupportedTypes)
                    . "'." . PHP_EOL . 'Supported types: ' . join(", ", array_keys($availableIndexers))
                );
            }
            $indexers = array_intersect_key($availableIndexers, array_flip($requestedTypes));
        }

        $allIndexers = $this->getAllIndexers();
        if (!array_diff_key($allIndexers, $indexers)) {
            return $indexers;
        }

        return array_intersect_key($allIndexers, array_flip(array_unique($indexers)));
    }

    /**
     * @return array
     */
    protected function getAllIndexers()
    {

        return \Licentia\Reports\Model\Indexer::AVAILABLE_INDEXES;
    }

    /**
     * Get list of options and arguments for the command
     *
     * @return mixed
     */
    public function getInputList()
    {

        return [
            new InputArgument(
                self::INDEXER_ARGUMENT,
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Space-separated list of index types or omit to apply to all indexes.'
            ),
        ];
    }
}
