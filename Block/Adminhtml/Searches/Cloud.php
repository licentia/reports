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
class Cloud extends \Magento\Backend\Block\Template
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
     * Cloud constructor.
     *
     * @param \Licentia\Reports\Model\Sales\StatsFactory  $ssalestatsFactory
     * @param \Magento\Framework\Pricing\Helper\Data      $priceHelper
     * @param \Licentia\Reports\Model\Search\StatsFactory $statsFactory
     * @param \Magento\Catalog\Model\ProductFactory       $productFactory
     * @param \Magento\Backend\Block\Template\Context     $context
     * @param array                                       $data
     */
    public function __construct(
        \Licentia\Reports\Model\Sales\StatsFactory $ssalestatsFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Licentia\Reports\Model\Search\StatsFactory $statsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {

        parent::__construct($context, $data);

        $this->priceHelper = $priceHelper;
        $this->relationsFactory = $statsFactory;
        $this->productFactory = $productFactory;
        $this->salesStats = $ssalestatsFactory;

        $this->setTemplate('Licentia_Reports::searches/cloud.phtml');
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
    public function getTagCloud()
    {

        if (isset($this->collection)) {
            return $this->collection;
        }

        $field = $this->getRequest()->getParam(
            'country',
            $this->getRequest()->getParam(
                'region',
                $this->getRequest()->getParam('age')
            )
        );

        $segmentId = $this->getRequest()->getParam('segment_id');
        $intervalStart = $this->getRequest()->getParam('interval_start');
        $intervalEnd = $this->getRequest()->getParam('interval_end');

        $collection = $this->relationsFactory->create()
                                             ->getTagCloud(
                                                 $this->getType(),
                                                 $field,
                                                 $segmentId,
                                                 $intervalStart,
                                                 $intervalEnd
                                             );

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
