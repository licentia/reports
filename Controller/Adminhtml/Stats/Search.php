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
