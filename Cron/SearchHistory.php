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

namespace Licentia\Reports\Cron;

/**
 * Class SearchHistory
 *
 * @package Licentia\Panda\Cron
 */
class SearchHistory
{

    /**
     * @var \Licentia\Panda\Helper\Data
     */
    protected $pandaHelper;

    /**
     * @var \Licentia\Reports\Model\ResourceModel\SearchFactory
     */
    protected $searchResource;

    /**
     * SearchHistory constructor.
     *
     * @param \Licentia\Reports\Model\ResourceModel\SearchFactory $searchResource
     * @param \Licentia\Panda\Helper\Data                         $pandaHelper
     */
    public function __construct(
        \Licentia\Reports\Model\ResourceModel\SearchFactory $searchResource,
        \Licentia\Panda\Helper\Data $pandaHelper
    ) {

        $this->searchResource = $searchResource;
        $this->pandaHelper = $pandaHelper;
    }

    /**
     * @return bool
     */
    public function execute()
    {

        try {
            $this->searchResource->create()->buildSearchGrid();
        } catch (\Exception $e) {
            $this->pandaHelper->logWarning($e);
        }

        return true;
    }
}
