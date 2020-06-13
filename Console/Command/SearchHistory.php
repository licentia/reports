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

namespace Licentia\Reports\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SearchHistory
 *
 * @package Licentia\Panda\Console\Command
 */
class SearchHistory extends Command
{

    /**
     * @var \Licentia\Reports\Model\ResourceModel\SearchFactory
     */
    protected $searchHistory;

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected $pandaHelper;

    /**
     * SearchHistory constructor.
     *
     * @param \Licentia\Reports\Helper\Data                       $pandaHelper
     * @param \Licentia\Reports\Model\ResourceModel\SearchFactory $searchFactory
     */
    public function __construct(
        \Licentia\Reports\Helper\Data $pandaHelper,
        \Licentia\Reports\Model\ResourceModel\SearchFactory $searchFactory
    ) {

        parent::__construct();
        $this->pandaHelper = $pandaHelper;
        $this->searchHistory = $searchFactory;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {

        $this->setName('panda:search:rebuild')
             ->setDescription('Rebuilds Search History Grid');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $start = date_create($this->pandaHelper->gmtDate());
        $output->writeln("SearchHistory | ");
        $output->writeln("SearchHistory | STARTED: " . $this->pandaHelper->gmtDate());

        try {
            $this->searchHistory->create()->buildSearchGrid();
        } catch (\Exception $e) {
            $output->writeln("<error>SearchHistory | " . $e->getMessage() . '</error>');
        }

        $end = date_create($this->pandaHelper->gmtDate());
        $diff = date_diff($end, $start);

        $output->writeln("SearchHistory | FINISHED: " . $this->pandaHelper->gmtDate());
        $output->writeln("SearchHistory | The process took " . $diff->format('%h Hours %i Minutes and %s Seconds'));
        $output->writeln("SearchHistory | ");
    }
}
