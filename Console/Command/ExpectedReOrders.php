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
 * Class ExpectedReOrders
 *
 * @package Licentia\Panda\Console\Command
 */
class ExpectedReOrders extends Command
{

    /**
     * @var \Licentia\Reports\Model\Sales\ExpectedReOrdersFactory
     */
    protected \Licentia\Reports\Model\Sales\ExpectedReOrdersFactory $expectedReOrders;

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected \Licentia\Reports\Helper\Data $pandaHelper;

    /**
     * ExpectedReOrders constructor.
     *
     * @param \Licentia\Reports\Helper\Data                         $pandaHelper
     * @param \Licentia\Reports\Model\Sales\ExpectedReOrdersFactory $pandaFactory
     */
    public function __construct(
        \Licentia\Reports\Helper\Data $pandaHelper,
        \Licentia\Reports\Model\Sales\ExpectedReOrdersFactory $pandaFactory
    ) {

        parent::__construct();
        $this->pandaHelper = $pandaHelper;
        $this->expectedReOrders = $pandaFactory;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {

        $this->setName('panda:reorders:rebuild')
             ->setDescription('Rebuilds Expected Sales');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $start = date_create($this->pandaHelper->gmtDate());
        $output->writeln("ExpectedReorders | ");
        $output->writeln("ExpectedReorders | STARTED: " . $this->pandaHelper->gmtDate());

        try {
            $this->expectedReOrders->create()->rebuild();
        } catch (\Exception $e) {
            $output->writeln("<error>ExpectedReorders | " . $e->getMessage() . '</error>');
        }

        $end = date_create($this->pandaHelper->gmtDate());
        $diff = date_diff($end, $start);

        $output->writeln("ExpectedReorders | FINISHED: " . $this->pandaHelper->gmtDate());

        $output->writeln("ExpectedReorders | The process took " . $diff->format('%h Hours %i Minutes and %s Seconds'));
        $output->writeln("ExpectedReorders | ");
    }
}
