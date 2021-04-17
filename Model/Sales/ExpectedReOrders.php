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

use Licentia\Reports\Model\Indexer;

/**
 * Class ExpectedOrders
 *
 * @package Licentia\Panda\Model\Sales
 */
class ExpectedReOrders extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'panda_sales_expected_reorders';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'panda_sales_expected_reorders';

    /**
     * @var \Licentia\Reports\Model\IndexerFactory
     */
    protected $indexer;

    /**
     * ExpectedReOrders constructor.
     *
     * @param \Licentia\Reports\Model\IndexerFactory                       $indexer
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Licentia\Reports\Model\IndexerFactory $indexer,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->indexer = $indexer->create();
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_init(\Licentia\Reports\Model\ResourceModel\Sales\ExpectedReOrders::class);
    }

    /**
     * @return ExpectedReOrders
     */
    public function reindexReorders()
    {

        $this->rebuild();

        return $this;
    }

    /**
     * @return $this
     */
    public function rebuild()
    {

        if (!$this->getData('consoleOutput') && !$this->indexer->canReindex('reorders')) {
            throw new \RuntimeException("Indexer status does not allow reindexing");
        }

        $this->indexer->updateIndexStatus(Indexer::STATUS_WORKING, 'reorders');

        $resource = $this->getResource();
        $connection = $resource->getConnection();

        $mainTable = $resource->getMainTable();

        $connection->delete($mainTable, ['locked=?' => 0]);
        $connection->query("SET group_concat_max_len=15000");

        $salesOrderTable = $resource->getTable('sales_order');
        $salesInvoiceTable = $resource->getTable('sales_invoice');
        $salesInvoiceItemTable = $resource->getTable('sales_invoice_item');

        $connection->query(
            "
                INSERT INTO $mainTable  
                    (item_id, num_sales, order_diff,qty_ordered, sku, name,customer_name,customer_email,customer_id," .
            "first_order,last_order,spent,expected_date,order_ids,locked)  
                    SELECT
                        sii.entity_id ,
                        count(*) AS num_sales ,
                        DATEDIFF(MAX(si.created_at) ,MIN(si.created_at)) /(COUNT(si.created_at) - 1) AS order_diff ,
                        SUM(sii.qty) as qty_ordered,
                        sii.sku ,
                        sii.`name` ,
                        CONCAT( so.customer_firstname , ' ' ,so.customer_lastname ) AS customer_name ,
                        so.customer_email ,
                        MAX(so.customer_id) AS customer_id ,
                        MIN(si.created_at) AS first_order ,
                        MAX(si.created_at) AS last_order ,
                        SUM(sii.base_row_total_incl_tax) AS spent ,
                        DATE_ADD(MAX(si.created_at) ,INTERVAL DATEDIFF(MAX(si.created_at), MIN(si.created_at)) / " .
            "(COUNT(si.created_at) - 1) DAY ) AS expected_date ,
                        GROUP_CONCAT(so.entity_id) AS order_ids, 0 as locked
                    FROM
                        $salesInvoiceItemTable AS sii
                    JOIN $salesInvoiceTable as si ON si.entity_id = sii.parent_id
                    JOIN $salesOrderTable so ON so.entity_id = si.order_id
                    where sku not IN 
                        (select sku FROM $mainTable WHERE customer_email = so.customer_email AND " .
            "customer_id=so.customer_id)
                    GROUP BY
                        so.customer_email ,
                        sii.sku
                    HAVING
                        order_diff > 5
                        AND expected_date >= NOW()
                        AND DATEDIFF(NOW() , last_order) >(order_diff - 5)
                        AND count(*) > 1"
        );

        $this->indexer->updateIndexStatus(Indexer::STATUS_VALID, 'reorders');

        return $this;
    }

    /**
     * @param $itemId
     *
     * @return $this
     */
    public function setItemId($itemId)
    {

        return $this->setData('item_id', $itemId);
    }

    /**
     * @param $numSales
     *
     * @return $this
     */
    public function setNumSales($numSales)
    {

        return $this->setData('num_sales', $numSales);
    }

    /**
     * @param $orderDiff
     *
     * @return $this
     */
    public function setOrderDiff($orderDiff)
    {

        return $this->setData('order_diff', $orderDiff);
    }

    /**
     * @param $qtyOrdered
     *
     * @return $this
     */
    public function setQtyOrdered($qtyOrdered)
    {

        return $this->setData('qty_ordered', $qtyOrdered);
    }

    /**
     * @param $sku
     *
     * @return $this
     */
    public function setSku($sku)
    {

        return $this->setData('sku', $sku);
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function setName($name)
    {

        return $this->setData('name', $name);
    }

    /**
     * @param $customerName
     *
     * @return $this
     */
    public function setCustomerName($customerName)
    {

        return $this->setData('customer_name', $customerName);
    }

    /**
     * @param $customerEmail
     *
     * @return $this
     */
    public function setCustomerEmail($customerEmail)
    {

        return $this->setData('customer_email', $customerEmail);
    }

    /**
     * @param $customerId
     *
     * @return $this
     */
    public function setCustomerId($customerId)
    {

        return $this->setData('customer_id', $customerId);
    }

    /**
     * @param $firstOrder
     *
     * @return $this
     */
    public function setFirstOrder($firstOrder)
    {

        return $this->setData('first_order', $firstOrder);
    }

    /**
     * @param $lastOrder
     *
     * @return $this
     */
    public function setLastOrder($lastOrder)
    {

        return $this->setData('last_order', $lastOrder);
    }

    /**
     * @param $spent
     *
     * @return $this
     */
    public function setSpent($spent)
    {

        return $this->setData('spent', $spent);
    }

    /**
     * @param $expectedDate
     *
     * @return $this
     */
    public function setExpectedDate($expectedDate)
    {

        return $this->setData('expected_date', $expectedDate);
    }

    /**
     * @param $orderIds
     *
     * @return $this
     */
    public function setOrderIds($orderIds)
    {

        return $this->setData('order_ids', $orderIds);
    }

    /**
     * @param $locked
     *
     * @return $this
     */
    public function setLocked($locked)
    {

        return $this->setData('locked', $locked);
    }

    /**
     * @return mixed
     */
    public function getItemId()
    {

        return $this->getData('item_id');
    }

    /**
     * @return mixed
     */
    public function getNumSales()
    {

        return $this->getData('num_sales');
    }

    /**
     * @return mixed
     */
    public function getOrderDiff()
    {

        return $this->getData('order_diff');
    }

    /**
     * @return mixed
     */
    public function getQtyOrdered()
    {

        return $this->getData('qty_ordered');
    }

    /**
     * @return mixed
     */
    public function getSku()
    {

        return $this->getData('sku');
    }

    /**
     * @return mixed
     */
    public function getName()
    {

        return $this->getData('name');
    }

    /**
     * @return mixed
     */
    public function getCustomerName()
    {

        return $this->getData('customer_name');
    }

    /**
     * @return mixed
     */
    public function getCustomerEmail()
    {

        return $this->getData('customer_email');
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {

        return $this->getData('customer_id');
    }

    /**
     * @return mixed
     */
    public function getFirstOrder()
    {

        return $this->getData('first_order');
    }

    /**
     * @return mixed
     */
    public function getLastOrder()
    {

        return $this->getData('last_order');
    }

    /**
     * @return mixed
     */
    public function getSpent()
    {

        return $this->getData('spent');
    }

    /**
     * @return mixed
     */
    public function getExpectedDate()
    {

        return $this->getData('expected_date');
    }

    /**
     * @return mixed
     */
    public function getOrderIds()
    {

        return $this->getData('order_ids');
    }

    /**
     * @return mixed
     */
    public function getLocked()
    {

        return $this->getData('locked');
    }
}
