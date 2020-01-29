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

namespace Licentia\Reports\Block\Adminhtml\Relations;

/**
 * Class Venn
 *
 * @package Licentia\Panda\Block\Adminhtml\Relations
 */
class Venn extends \Magento\Backend\Block\Template
{

    /**
     * @var \Licentia\Reports\Model\Products\RelationsFactory
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
    protected $skus;

    /**
     * @var
     */
    protected $sku;

    /**
     * @var
     */
    protected $skusNames;

    /**
     * @var \Licentia\Equity\Model\SegmentsFactory
     */
    protected $segmentsFactory;

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
    protected $type;

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
    protected $salesStats;

    /**
     * Venn constructor.
     *
     * @param \Licentia\Reports\Model\Sales\StatsFactory        $statsFactory
     * @param \Magento\Framework\Pricing\Helper\Data            $priceHelper
     * @param \Licentia\Reports\Model\Products\RelationsFactory $relationsFactory
     * @param \Licentia\Equity\Model\SegmentsFactory            $segmentsFactory
     * @param \Magento\Catalog\Model\ProductFactory             $productFactory
     * @param \Magento\Backend\Block\Template\Context           $context
     * @param array                                             $data
     */
    public function __construct(
        \Licentia\Reports\Model\Sales\StatsFactory $statsFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Licentia\Reports\Model\Products\RelationsFactory $relationsFactory,
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

        $this->setTemplate('Licentia_Reports::relations/venn.phtml');
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

        if ($this->skus) {
            return $this->skus;
        }

        $return = [];
        for ($i = 1; $i <= 5; $i++) {
            if ($sku = $this->getRequest()->getParam('sku' . $i)) {
                if ($this->getRequest()->getParam('attributes') == 1) {
                    $return[$sku] = $this->getAttributeName($sku);
                } else {
                    $return[$sku] = $this->getProduct($sku);
                }
            }
        }

        $this->skus = array_combine(array_keys($return), array_keys($return));
        $this->skusNames = $return;

        return $this->skus;
    }

    /**
     * @param bool|array $skus
     *
     * @return array
     */
    public function getSkuNames($skus = false)
    {

        if ($skus == false) {
            $this->getSKUs();
        } else {
            $return = [];
            foreach ($skus as $sku) {
                if ($this->getRequest()->getParam('attributes') == 1) {
                    $return[$sku] = $this->getAttributeName($sku);
                } else {
                    $return[$sku] = $this->getProduct($sku);
                }
            }
            $this->skusNames = $return;
        }

        return $this->skusNames;
    }

    /**
     * @return mixed
     */
    public function getVennData()
    {

        $sku = $this->getSKUs();

        $type = $this->getType();

        $region = $this->getRegion();
        $country = $this->getCountry();
        $filterField = [];
        if ($region) {
            $type = 'regions';
            $filterField = ['region=?' => $region];
        }

        if ($country) {
            $type = 'countries';
            $filterField = ['country=?' => $country];
        }

        $this->type = $type;

        if (isset($this->collectionVenn)) {
            return $this->collectionVenn;
        }

        $segmentId = $this->getSegmentId();

        $collection = $this->relationsFactory->create()
                                             ->getVennData(
                                                 $sku,
                                                 $type,
                                                 $segmentId,
                                                 $filterField,
                                                 $this->isAttributes(),
                                                 $this->getAttribute()
                                             );

        $this->collectionVenn = $collection;

        return $this->collectionVenn;
    }

    /**
     * @return bool
     */
    /**
     * @return bool
     */
    public function isAttributes()
    {

        return $this->getRequest()->getParam('attributes') == 1;
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
                                           'country',
                                           $this->getSegmentId(),
                                           $this->isAttributes()
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
                                           'region',
                                           $this->getSegmentId(),
                                           $this->isAttributes()
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

        $this->country = in_array($country, $this->getPossibleCountries()) ? $country : null;

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

        $this->region = in_array($region, $this->getPossibleRegions()) ? $region : null;

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
     * @param $sku
     *
     * @return bool|\Magento\Catalog\Model\AbstractModel
     */
    public function getProduct($sku)
    {

        $product = $this->productFactory->create()->loadByAttribute('sku', $sku);

        if ($product) {
            return $product->getName();
        }

        return $sku;
    }

    /**
     * @param $sku
     *
     * @return bool|\Magento\Catalog\Model\AbstractModel
     */
    public function getAttributeName($sku)
    {

        return $this->relationsFactory->create()->getAttributeName($sku);
    }

    /**
     * @return array|mixed
     */
    /**
     * @return array|mixed
     */
    public function getAttribute()
    {

        $attribute = $this->getRequest()->getParam('attribute');

        if (!$attribute) {
            $attribute = explode(
                ',',
                $this->_scopeConfig->getValue(
                    'panda_equity/reports/attributes',
                    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
                )
            );
            $attribute = reset($attribute);
        }

        return $attribute;
    }

    /**
     * @return array
     */
    public function getPossibleAttributesValues()
    {

        if ($this->possibleAttributesValues) {
            return $this->possibleAttributesValues;
        }
        $info = $this->relationsFactory->create()->getDistinctAttributesValues($this->getAttribute());

        $this->possibleAttributesValues = $info;

        return $this->possibleAttributesValues;
    }

    /**
     * @return array
     */
    public function getPossibleAttributes()
    {

        if ($this->possibleAttributes) {
            return $this->possibleAttributes;
        }
        $info = $this->relationsFactory->create()->getPossibleAttributes();

        $this->possibleAttributes = $info;

        return $this->possibleAttributes;
    }
}
