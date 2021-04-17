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

namespace Licentia\Reports\Block\Adminhtml\Searches;

/**
 * Class Venn
 *
 * @package Licentia\Panda\Block\Adminhtml\Searches
 */
class Venn extends \Magento\Backend\Block\Template
{

    /**
     * @var \Licentia\Reports\Model\Search\StatsFactory
     */
    protected \Licentia\Reports\Model\Search\StatsFactory $relationsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected \Magento\Catalog\Model\ProductFactory $productFactory;

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
    protected \Magento\Framework\Pricing\Helper\Data $priceHelper;

    /**
     * @var
     */
    protected array $queries;

    /**
     * @var
     */
    protected $query;

    /**
     * @var
     */
    protected $queriesNames;

    /**
     * @var \Licentia\Equity\Model\SegmentsFactory
     */
    protected \Licentia\Equity\Model\SegmentsFactory $segmentsFactory;

    /**
     * @var
     */
    protected $country;

    /**
     * @var
     */
    protected $possibleRegions;

    /**
     * @var
     */
    protected $possibleCountries;

    /**
     * @var
     */
    protected $region;

    /**
     * @var
     */
    protected string $type;

    /**
     * @var
     */
    protected $possibleAttributes;

    /**
     * @var
     */
    protected $possibleAttributesValues;

    /**
     * @var \Licentia\Reports\Model\Sales\StatsFactory
     */
    protected \Licentia\Reports\Model\Sales\StatsFactory $salesStats;

    /**
     * Venn constructor.
     *
     * @param \Licentia\Reports\Model\Sales\StatsFactory  $statsFactory
     * @param \Magento\Framework\Pricing\Helper\Data      $priceHelper
     * @param \Licentia\Reports\Model\Search\StatsFactory $relationsFactory
     * @param \Licentia\Equity\Model\SegmentsFactory      $segmentsFactory
     * @param \Magento\Catalog\Model\ProductFactory       $productFactory
     * @param \Magento\Backend\Block\Template\Context     $context
     * @param array                                       $data
     */
    public function __construct(
        \Licentia\Reports\Model\Sales\StatsFactory $statsFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Licentia\Reports\Model\Search\StatsFactory $relationsFactory,
        \Licentia\Equity\Model\SegmentsFactory $segmentsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {

        parent::__construct($context, $data);

        $this->priceHelper = $priceHelper;
        $this->relationsFactory = $relationsFactory;
        $this->productFactory = $productFactory;
        $this->segmentsFactory = $segmentsFactory;
        $this->salesStats = $statsFactory;

        $this->setTemplate('Licentia_Reports::searches/venn.phtml');
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
    public function getQueries()
    {

        if ($this->queries) {
            return $this->queries;
        }

        $return = [];
        for ($i = 1; $i <= 5; $i++) {
            if ($query = $this->getRequest()->getParam('query' . $i)) {
                $return[$query] = $query;
            }
        }

        $this->queries = array_combine(array_keys($return), array_keys($return));
        $this->queriesNames = $return;

        return $this->queries;
    }

    /**
     * @param bool|array $queries
     *
     * @return array
     */
    public function getQueryNames($queries = false)
    {

        if ($queries == false) {
            $this->getQueries();
        } else {
            $return = [];
            foreach ($queries as $query) {
                $return[$query] = $query;
            }
            $this->queriesNames = $return;
        }

        return $this->queriesNames;
    }

    /**
     * @return mixed
     */
    public function getVennData()
    {

        $query = $this->getQueries();

        $type = $this->getType();

        $region = $this->getRegion();
        $country = $this->getCountry();
        $filterField = [];
        if ($region) {
            $type = 'region';
            $filterField = ['region=?' => $region];
        }

        if ($country) {
            $type = 'country';
            $filterField = ['country=?' => $country];
        }

        $this->type = $type;

        if (isset($this->collectionVenn)) {
            return $this->collectionVenn;
        }

        $segmentId = $this->getSegmentId();

        $collection = $this->relationsFactory->create()->getVennData($query, $type, $segmentId, $filterField);

        $this->collectionVenn = $collection;

        return $this->collectionVenn;
    }

    /**
     * @param string $country
     *
     * @return string
     */
    /**
     * @param string $country
     *
     * @return string
     */
    public function countryExists($country = '')
    {

        return $this->relationsFactory->create()->countryExists($country);
    }

    /**
     * @param string $region
     *
     * @return string
     */
    /**
     * @param string $region
     *
     * @return string
     */
    public function regionExists($region = '')
    {

        return $this->relationsFactory->create()->regionExists($region);
    }

    /**
     * @return mixed
     */
    public function getPossibleCountries()
    {

        if ($this->possibleCountries) {
            return $this->possibleCountries;
        }
        $info = $this->relationsFactory->create()
                                       ->getPossibleVennOptions(
                                           'country',
                                           $this->getSegmentId()
                                       );

        if (in_array('UK', $info)) {
            $key = array_search('UK', $info);
            unset($info[$key]);
            $info[$key] = 'GB';
        }

        $this->possibleCountries = $info;

        return $this->possibleCountries;
    }

    /**
     * @return mixed
     */
    public function getPossibleRegions()
    {

        if ($this->possibleRegions) {
            return $this->possibleRegions;
        }

        $info = $this->relationsFactory->create()
                                       ->getPossibleVennOptions(
                                           'region',
                                           $this->getSegmentId(),
                                           $this->getQueries()
                                       );

        $this->possibleRegions = $info;

        return $this->possibleRegions;
    }

    /**
     * @return mixed|null
     */
    public function getCountry()
    {

        if ($this->country) {
            return $this->country;
        }

        $country = $this->getRequest()->getParam('country');

        $this->country = $this->countryExists($country) ? $country : null;

        return $this->country;
    }

    /**
     * @return mixed|null
     */
    public function getRegion()
    {

        if ($this->region) {
            return $this->region;
        }

        $region = $this->getRequest()->getParam('region');

        $this->region = $this->regionExists($region) ? $region : null;

        return $this->region;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getSegmentsList()
    {

        $collection = $this->segmentsFactory->create()
                                            ->getCollection()
                                            ->addFieldToSelect(['segment_id', 'name'])
                                            ->addFieldToFilter('products_relations', 1)
                                            ->setOrder('name', 'ASC');

        return $collection;
    }

    /**
     * @return mixed|null
     */
    public function getSegmentId()
    {

        $segmentId = $this->getRequest()->getParam('segment_id');

        if ($segmentId) {
            return $this->segmentsFactory->create()
                                         ->load($segmentId)
                                         ->getId();
        }

        return null;
    }

    /**
     * @param $query
     *
     * @return bool|\Magento\Catalog\Model\AbstractModel
     */
    public function getProduct($query)
    {

        return $this->productFactory->create()
                                    ->loadByAttribute('query', $query)
                                    ->getName();
    }
}
