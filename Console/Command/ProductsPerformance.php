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
 * Class ProductsPerformance
 *
 * @package Licentia\Panda\Console\Command
 */
class ProductsPerformance extends Command
{

    /**
     * @var \Licentia\Reports\Model\Sales\StatsFactory
     */
    protected $statsFactory;

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected $pandaHelper;

    /**
     * ProductsPerformance constructor.
     *
     * @param \Licentia\Reports\Helper\Data              $pandaHelper
     * @param \Licentia\Reports\Model\Sales\StatsFactory $pandaFactory
     */
    public function __construct(
        \Licentia\Reports\Helper\Data $pandaHelper,
        \Licentia\Reports\Model\Sales\StatsFactory $pandaFactory
    ) {

        parent::__construct();
        $this->pandaHelper = $pandaHelper;
        $this->statsFactory = $pandaFactory;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {

        $this->setName('panda:performance:rebuild')
             ->setDescription('Rebuilds Products Performance');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $start = date_create($this->pandaHelper->gmtDate());
        $output->writeln("Performance | ");
        $output->writeln("Performance | STARTED: " . $this->pandaHelper->gmtDate());

        try {
            $this->statsFactory->create()
                               ->setData('consoleOutput', $output)
                               ->rebuildAll();
        } catch (\Exception $e) {
            $output->writeln("Performance | ERROR");
            $output->writeln("Performance | ERROR: " . $e->getMessage());
            $output->writeln("Performance | ERROR");
        }

        $end = date_create($this->pandaHelper->gmtDate());
        $diff = date_diff($end, $start);

        $output->writeln("Performance | FINISHED: " . $this->pandaHelper->gmtDate());
        $output->writeln("Performance | The process took " . $diff->format('%h Hours %i Minutes and %s Seconds'));
        $output->writeln("Performance | ");
    }
}
