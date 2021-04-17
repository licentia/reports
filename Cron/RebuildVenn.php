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
 * Class Venn
 *
 * @package Licentia\Panda\Cron
 */
class RebuildVenn
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    /**
     * @var \Licentia\Panda\Helper\Data
     */
    protected \Licentia\Panda\Helper\Data $pandaHelper;

    /**
     * @var \Licentia\Reports\Model\Products\RelationsFactory
     */
    protected \Licentia\Reports\Model\Products\RelationsFactory $relations;

    /**
     * RebuildVenn constructor.
     *
     * @param \Licentia\Reports\Model\Products\RelationsFactory  $relationsFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
     * @param \Licentia\Panda\Helper\Data                        $pandaHelper
     */
    public function __construct(
        \Licentia\Reports\Model\Products\RelationsFactory $relationsFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Licentia\Panda\Helper\Data $pandaHelper
    ) {

        $this->relations = $relationsFactory;
        $this->scopeConfig = $scopeConfigInterface;
        $this->pandaHelper = $pandaHelper;
    }

    /**
     * @return $this
     */
    public function execute()
    {

        try {
            $this->relations->create()->rebuildVennAll();
        } catch (\Exception $e) {
            $this->pandaHelper->logWarning($e);
        }

        return $this;
    }
}
