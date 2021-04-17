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

namespace Licentia\Reports\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action;

/**
 * Class Search
 *
 * @package Licentia\Panda\Controller\Adminhtml\Ajax
 */
class Search extends Action
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected ?\Magento\Framework\Registry $registry = null;

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected \Licentia\Reports\Helper\Data $pandaHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected \Magento\Catalog\Model\ProductFactory $productFactory;

    /**
     * @var \Licentia\Reports\Model\SearchFactory
     */
    protected \Licentia\Reports\Model\SearchFactory $searchFactory;

    /**
     * @param Action\Context                                   $context
     * @param \Licentia\Reports\Model\SearchFactory            $searchFactory
     * @param \Magento\Catalog\Model\ProductFactory            $productFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Registry                      $registry
     * @param \Licentia\Reports\Helper\Data                    $pandaHelper
     */
    public function __construct(
        Action\Context $context,
        \Licentia\Reports\Model\SearchFactory $searchFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Registry $registry,
        \Licentia\Reports\Helper\Data $pandaHelper
    ) {

        $this->registry = $registry;
        $this->pandaHelper = $pandaHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productFactory = $productFactory;
        $this->searchFactory = $searchFactory;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $type = $this->getRequest()->getParam('type', 'product');

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();

        $term = $this->getRequest()->getParam('term');
        $return = [];
        if ($type == 'product') {
            $filter = [
                ['attribute' => 'sku', 'like' => "%$term%"],
                ['attribute' => 'name', 'like' => "%$term%"],
            ];

            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $products */
            $products = $this->productFactory->create()
                                             ->getCollection()
                                             ->addAttributeToSelect('name')
                                             ->addAttributeToSelect('sku')
                                             ->addAttributeToFilter($filter)
                                             ->setPageSize(20);

            $term = $products->getConnection()->quote($term);
            $products->getSelect()
                     ->columns(['exact' => new \Zend_Db_Expr("IF(e.sku=$term, 1, IF(at_name.value=$term, 1, 0))")])
                     ->order('exact DESC');

            foreach ($products as $product) {
                $return[] = [
                    'id'    => $product->getSku(),
                    'value' => $product->getSku(),
                    'label' => $product->getSku() . ' - ' . $product->getName(),
                ];
            }
        }
        if ($type == 'search') {
            $products = $this->searchFactory->create()->getMetadataSearchArray($term);
            foreach ($products as $product) {
                $return[] = [
                    'id'    => $product,
                    'value' => $product,
                    'label' => $product,
                ];
            }
        }

        if (!$products) {
            $return[] = [
                'id'    => '',
                'value' => '',
                'label' => __('No Records'),
            ];
        }

        return $result->setData($return);
    }
}
