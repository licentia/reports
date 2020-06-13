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

namespace Licentia\Reports\Block\Adminhtml\Searches;

/**
 * Class View
 *
 * @package Licentia\Panda\Block\Adminhtml
 */

/**
 * Class View
 *
 * @package Licentia\Panda\Block\Adminhtml\Searches
 */
class View extends \Magento\Backend\Block\Template
{

    /**
     * @var \Licentia\Reports\Model\Search\StatsFactory
     */
    protected $relationsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var
     */
    protected $collection;

    /**
     * @var
     */
    protected $collectionVenn;

    /**
     * @var
     */
    protected $collectionPeriods;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var
     */
    protected $queries;

    /**
     * @var
     */
    protected $query;

    /**
     * @var
     */
    protected $possibleAttributes;

    /**
     * @var
     */
    protected $type;

    /**
     * @var \Licentia\Reports\Model\Sales\StatsFactory
     */
    protected $salesStats;

    /**
     * View constructor.
     *
     * @param \Magento\Framework\Pricing\Helper\Data      $priceHelper
     * @param \Licentia\Reports\Model\Search\StatsFactory $statsFactory
     * @param \Magento\Catalog\Model\ProductFactory       $productFactory
     * @param \Magento\Backend\Block\Template\Context     $context
     * @param \Licentia\Reports\Model\Sales\StatsFactory  $salesStatsFactory
     * @param array                                       $data
     */
    public function __construct(
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Licentia\Reports\Model\Search\StatsFactory $statsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Licentia\Reports\Model\Sales\StatsFactory $salesStatsFactory,
        array $data = []
    ) {

        parent::__construct($context, $data);

        $this->priceHelper = $priceHelper;
        $this->relationsFactory = $statsFactory;
        $this->productFactory = $productFactory;
        $this->salesStats = $salesStatsFactory;

        $this->setTemplate('Licentia_Reports::searches/view.phtml');
    }

    /**
     * @return array
     */
    public function getGroups()
    {

        return \Licentia\Reports\Model\Search\Stats::getGroups();
    }

    /**
     * @return string
     */
    public function getGroup()
    {

        $type = strtolower(
            $this->getRequest()->getParam('group', 'date')
        );

        if (!in_array($type, array_keys($this->getGroups()))) {
            $type = 'day';
        }

        return $type;
    }

    /**
     * @return array
     */
    public function getTypes()
    {

        return $this->salesStats->create()->getTypes();
    }

    /**
     * @return string
     */
    public function getType()
    {

        $type = strtolower(
            $this->getRequest()->getParam('type', 'global')
        );

        if (!in_array($type, array_keys($this->getTypes()))) {
            $type = 'global';
        }

        return $type;
    }

    /**
     * @return array
     */
    public function getSKUs()
    {

        if ($this->queries) {
            return $this->queries;
        }

        $return = [];
        for ($i = 1; $i <= 5; $i++) {
            if ($sku = $this->getRequest()->getParam('query' . $i)) {
                $return[$sku] = $sku;
            }
        }

        $this->queries = $return;

        return $this->queries;
    }

    /**
     * @return array
     */
    public function getSKU()
    {

        if ($this->query) {
            return $this->query;
        }

        $sku = $this->getRequest()->getParam('query');

        $this->query = $sku;

        return $this->query;
    }

    /**
     * @return mixed
     */
    public function getStatsArray()
    {

        $sku = $this->getSKU();

        if (!$sku) {
            return [];
        }

        $type = $this->getType();

        $this->type = $type;

        if (isset($this->collection)) {
            return $this->collection;
        }

        $collection = $this->relationsFactory->create()->getStatsCollection($sku);

        $this->collection = $collection;

        return $this->collection;
    }

    /**
     * @param $sku
     *
     * @return bool|\Magento\Catalog\Model\AbstractModel
     */
    public function getProduct($sku)
    {

        return $this->productFactory->create()->loadByAttribute('sku', $sku);
    }

    /**
     * @param $sku
     *
     * @return string
     */
    public function getProductName($sku)
    {

        return $sku;
    }
}
