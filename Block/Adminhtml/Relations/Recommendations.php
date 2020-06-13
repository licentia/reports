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

namespace Licentia\Reports\Block\Adminhtml\Relations;

/**
 * Class Recommendations
 *
 * @package Licentia\Panda\Block\Adminhtml\Relations
 */

/**
 * Class Recommendations
 *
 * @package Licentia\Panda\Block\Adminhtml\Relations
 */
class Recommendations extends \Magento\Backend\Block\Template
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
     * @var \Licentia\Reports\Model\Sales\StatsFactory
     */
    protected $salesStats;

    /**
     * Recommendations constructor.
     *
     * @param \Licentia\Reports\Model\Sales\StatsFactory        $statsFactory
     * @param \Magento\Framework\Pricing\Helper\Data            $priceHelper
     * @param \Licentia\Reports\Model\Products\RelationsFactory $relationsFactory
     * @param \Magento\Catalog\Model\ProductFactory             $productFactory
     * @param \Magento\Backend\Block\Template\Context           $context
     * @param array                                             $data
     */
    public function __construct(
        \Licentia\Reports\Model\Sales\StatsFactory $statsFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Licentia\Reports\Model\Products\RelationsFactory $relationsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {

        parent::__construct($context, $data);

        $this->priceHelper = $priceHelper;
        $this->relationsFactory = $relationsFactory;
        $this->productFactory = $productFactory;
        $this->salesStats = $statsFactory;

        $this->setTemplate('Licentia_Reports::relations/recommendations.phtml');
    }

    /**
     * @return array
     */
    public function getGroups()
    {

        return $this->salesStats->create()->getGroups();
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

        if ($this->skus) {
            return $this->skus;
        }

        $return = [];
        for ($i = 1; $i <= 5; $i++) {
            if ($sku = $this->getRequest()->getParam('sku' . $i)) {
                if ($this->getProduct($sku)) {
                    $return[$sku] = $sku;
                }
            }
        }

        $this->skus = $return;

        return $this->skus;
    }

    /**
     * @return array
     */
    public function getSKU()
    {

        if ($this->sku) {
            return $this->sku;
        }

        if ($sku = $this->getRequest()->getParam('sku')) {
            preg_match('/.*\s\((.*)\)\s\[\d{1,}\]$/', $sku, $result);

            if (isset($result[1])) {
                $sku = $result[1];
            }

            if (!$this->getProduct($sku)) {
                $sku = false;
            }
        }

        $this->sku = $sku;

        return $this->sku;
    }

    /**
     * @return mixed
     */
    public function getStatsArray()
    {

        $sku = $this->getSKU();

        $type = $this->getType();

        $this->_type = $type;

        if (isset($this->collection)) {
            return $this->collection;
        }

        $collection = $this->relationsFactory->create()->getRecommendationsCollection($sku);

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

        return $this->relationsFactory->create()->getProductName($sku);
    }
}
