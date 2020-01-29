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
