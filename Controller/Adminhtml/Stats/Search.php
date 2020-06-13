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

namespace Licentia\Reports\Controller\Adminhtml\Stats;

/**
 * Class Index
 *
 * @package Licentia\Panda\Controller\Adminhtml\Stats
 */
class Search extends \Licentia\Reports\Controller\Adminhtml\Stats
{

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();

        $term = $this->getRequest()->getParam('term');

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

        $return = [];
        foreach ($products as $product) {
            $return[] = [
                'id'    => $product->getSku(),
                'value' => $product->getSku(),
                'label' => $product->getSku() . ' - ' . $product->getName(),
            ];
        }

        return $result->setData($return);
    }
}
