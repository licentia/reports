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
 * Class Recommendations
 *
 * @package Licentia\Panda\Console\Command
 */
class Recommendations extends Command
{

    /**
     * @var \Licentia\Reports\Model\Products\RelationsFactory
     */
    protected $relationsFactory;

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected $pandaHelper;

    /**
     * Recommendations constructor.
     *
     * @param \Licentia\Reports\Helper\Data                     $pandaHelper
     * @param \Licentia\Reports\Model\Products\RelationsFactory $pandaFactory
     */
    public function __construct(
        \Licentia\Reports\Helper\Data $pandaHelper,
        \Licentia\Reports\Model\Products\RelationsFactory $pandaFactory
    ) {

        parent::__construct();
        $this->pandaHelper = $pandaHelper;
        $this->relationsFactory = $pandaFactory;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {

        $this->setName('panda:recommendations:rebuild')
             ->setDescription('Rebuilds Products Recommendations');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $start = date_create($this->pandaHelper->gmtDate());
        $output->writeln("Recommendations | ");
        $output->writeln("Recommendations | STARTED: " . $this->pandaHelper->gmtDate());

        try {
            $this->relationsFactory->create()
                                   ->setData('consoleOutput', $output)
                                   ->rebuildAllRecommendations();
        } catch (\Exception $e) {
            $output->writeln("<error>Recommendations | " . $e->getMessage() . '</error>');
        }

        $end = date_create($this->pandaHelper->gmtDate());
        $diff = date_diff($end, $start);

        $output->writeln("Recommendations | FINISHED: " . $this->pandaHelper->gmtDate());
        $output->writeln("Recommendations | The process took " . $diff->format('%h Hours %i Minutes and %s Seconds'));
        $output->writeln("Recommendations | ");
    }
}
