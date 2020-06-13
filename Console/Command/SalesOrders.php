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
 * Class SalesOrders
 *
 * @package Licentia\Panda\Console\Command
 */
class SalesOrders extends Command
{

    /**
     * @var \Licentia\Reports\Model\Sales\OrdersFactory
     */
    protected $salesOrders;

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected $pandaHelper;

    /**
     * SalesOrders constructor.
     *
     * @param \Licentia\Reports\Helper\Data               $pandaHelper
     * @param \Licentia\Reports\Model\Sales\OrdersFactory $ordersFactory
     */
    public function __construct(
        \Licentia\Reports\Helper\Data $pandaHelper,
        \Licentia\Reports\Model\Sales\OrdersFactory $ordersFactory
    ) {

        parent::__construct();
        $this->pandaHelper = $pandaHelper;
        $this->salesOrders = $ordersFactory;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {

        $this->setName('panda:sales:rebuild')
             ->setDescription('Rebuilds Sales Orders Stats');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $start = date_create($this->pandaHelper->gmtDate());
        $output->writeln("SalesOrders | ");
        $output->writeln("SalesOrders | STARTED: " . $this->pandaHelper->gmtDate());

        try {
            $this->salesOrders->create()
                              ->setData('consoleOutput', $output)
                              ->rebuildAll();
        } catch (\Exception $e) {
            $output->writeln("<error>SalesOrders | " . $e->getMessage() . '</error>');
        }

        $end = date_create($this->pandaHelper->gmtDate());
        $diff = date_diff($end, $start);

        $output->writeln("SalesOrders | FINISHED: " . $this->pandaHelper->gmtDate());
        $output->writeln("SalesOrders | The process took " . $diff->format('%h Hours %i Minutes and %s Seconds'));
        $output->writeln("SalesOrders | ");
    }
}
