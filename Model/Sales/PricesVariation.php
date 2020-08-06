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

namespace Licentia\Reports\Model\Sales;

/**
 * Class PricesVariation
 *
 * @package Licentia\Equity\Model\Sales
 */
class PricesVariation extends \Magento\Framework\Model\AbstractModel
    implements \Licentia\Reports\Api\Data\PricesVariationsInterface
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'panda_prices_variation';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'panda_prices_variation';

    /**
     * @var \Licentia\Equity\Helper\Data
     */
    protected $pandaHelper;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_init(\Licentia\Reports\Model\ResourceModel\Sales\PricesVariation::class);
    }

    /**
     * PricesVariation constructor.
     *
     * @param \Licentia\Equity\Helper\Data                                 $pandaHelper
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Licentia\Equity\Helper\Data $pandaHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->pandaHelper = $pandaHelper;
    }

    /**
     * @return $this
     */
    public function clearPricesVariation()
    {

        $this->getResource()->getConnection()->truncateTable($this->getResource()->getTable('panda_prices_variation'));

        return $this;
    }

    /**
     * @return $this
     */
    public function updatePricesVariation()
    {

        $resource = $this->getResource();
        $connection = $resource->getConnection();

        $salesInvoiceTable = $resource->getTable('sales_invoice');
        $salesInvoiceItemTable = $resource->getTable('sales_invoice_item');
        $salesOrderTable = $resource->getTable('sales_order');

        $groups = $connection->fetchAll(
            $connection->select()
                       ->from($resource->getTable('panda_prices_variation_groups'))
        );

        if (!$groups) {
            $groups = [['item_id' => -1, 'groups' => '']];
        }

        $lastInvoiceId = (int) $connection->fetchOne(
            $connection->select()
                       ->from($resource->getTable('panda_prices_variation'), [new \Zend_Db_Expr('MAX(invoice_id)')])
                       ->limit(1)
        );

        foreach ($groups as $group) {

            $list = explode(',', $group['groups']);
            $list = array_map('trim', $list);

            $groupId = $group['item_id'];

            $select = $connection->select()
                                 ->from(['sii' => $salesInvoiceItemTable], [new \Zend_Db_Expr('DISTINCT(sku)')])
                                 ->joinLeft(['si' => $salesInvoiceTable], 'si.entity_id = sii.parent_id', [])
                                 ->joinLeft(['so' => $salesOrderTable], 'si.order_id = so.entity_id', [])
                                 ->where('sii.parent_id >?', $lastInvoiceId);

            if ($groupId != -1) {
                $select->where('so.customer_group_id IN (?)', $list);
            }

            $skusSold = $connection->fetchCol($select);

            foreach ($skusSold as $sku) {

                $connection->delete($resource->getTable('panda_prices_variation'), ['sku=?' => $sku]);

                $selectColumns = [];
                $selectColumns['month'] = new \Zend_Db_Expr("MONTH(si.created_at)");
                $selectColumns['year'] = new \Zend_Db_Expr("YEAR(si.created_at)");
                $selectColumns['sold_at'] = new \Zend_Db_Expr("date_format(si.created_at,'%Y-%m-%d')");
                $selectColumns['day'] = new \Zend_Db_Expr("date_format(si.created_at,'%d')");
                $selectColumns['weekday'] = new \Zend_Db_Expr("date_format(si.created_at,'%w')");
                $selectColumns['store_id'] = "so.store_id";
                $selectColumns['day_year'] = new \Zend_Db_Expr("date_format(si.created_at,'%j')");

                $selectMain = $connection->select()
                                         ->from(['sii' => $salesInvoiceItemTable],
                                             [
                                                 'price'      => 'sii.base_price',
                                                 'sii.qty',
                                                 'invoice_id' => 'si.entity_id',
                                                 'sii.entity_id',
                                                 'sii.name',
                                             ])
                                         ->joinLeft(['si' => $salesInvoiceTable], 'si.entity_id = ' . 'sii.parent_id',
                                             $selectColumns)
                                         ->joinLeft(['so' => $salesOrderTable], 'si.order_id = so.entity_id', [])
                                         ->where('sku=?', $sku)
                                         ->order('si.entity_id');

                if ($groupId != -1) {
                    $selectMain->where('so.customer_group_id IN (?)', $list);
                }

                $items = $connection->fetchAll($selectMain);

                $i = 0;
                $a = 0;
                foreach ($items as $item) {

                    if ($i == 0) {
                        $insert = $item;
                        $insert['first_sale_at'] = $insert['sold_at'];
                        $insert['group_id'] = $groupId == -1 ? null : $groupId;
                        unset($insert['entity_id']);
                        $insert['sku'] = $sku;
                    }

                    $insert['invoice_id'] = $item['invoice_id'];
                    $insert['last_sale_at'] = $item['sold_at'];
                    unset($insert['sold_at']);

                    if (($i && $item['price'] != $items[$a]['price']) || ($a + 1) == count($items)) {

                        $insert['deviation'] = $connection->fetchOne(
                            $connection->select()
                                       ->from($resource->getTable('sales_invoice_item'),
                                           ['deviation' => new \Zend_Db_Expr('FORMAT(STDDEV(base_price),0)')])
                                       ->where('sku=?', $sku)
                                       ->where('entity_id<=?', $item['entity_id'])
                        );

                        $connection->insert($resource->getTable('panda_prices_variation'), $insert);

                        unset($insert);
                        $i = 0;
                        $a++;

                        continue;

                    } else {
                        $insert['qty'] += $item['qty'];
                    }

                    $i++;
                    $a++;
                }

            }
        }

        return $this;
    }

    /**
     * @param $sku
     *
     * @return $this
     */
    public function setSku($sku)
    {

        return $this->setData(self::SKU, $sku);
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function setName($name)
    {

        return $this->setData(self::NAME, $name);
    }

    /**
     * @param $price
     *
     * @return $this
     */
    public function setPrice($price)
    {

        return $this->setData(self::PRICE, $price);
    }

    /**
     * @param $firstSaleAt
     *
     * @return $this
     */
    public function setFirstSaleAt($firstSaleAt)
    {

        return $this->setData(self::FIRST_SALE_AT, $firstSaleAt);
    }

    /**
     * @param $lastSaleAt
     *
     * @return $this
     */
    public function setLastSaleAt($lastSaleAt)
    {

        return $this->setData(self::LAST_SALE_AT, $lastSaleAt);
    }

    /**
     * @param $deviation
     *
     * @return $this
     */
    public function setDeviation($deviation)
    {

        return $this->setData(self::DEVIATION, $deviation);
    }

    /**
     * @return mixed
     */
    public function getSku()
    {

        return $this->getData(self::SKU);
    }

    /**
     * @return mixed
     */
    public function getName()
    {

        return $this->getData(self::NAME);
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {

        return $this->getData(self::PRICE);
    }

    /**
     * @return mixed
     */
    public function getFirstSaleAt()
    {

        return $this->getData(self::FIRST_SALE_AT);
    }

    /**
     * @return mixed
     */
    public function getLastSaleAt()
    {

        return $this->getData(self::LAST_SALE_AT);
    }

    /**
     * @return mixed
     */
    public function getDeviation()
    {

        return $this->getData(self::DEVIATION);
    }

    /**
     * @param $qty
     *
     * @return $this
     */
    public function setQty($qty)
    {

        return $this->setData(self::QTY, $qty);
    }

    /**
     * @return mixed
     */
    public function getQty()
    {

        return $this->getData(self::QTY);
    }
}
