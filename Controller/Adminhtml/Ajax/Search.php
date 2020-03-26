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
 * @modified   26/03/20, 23:29 GMT
 *
 */

namespace Licentia\Reports\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action;

/**
 * Class Search
 *
 * @package Licentia\Panda\Controller\Adminhtml\Ajax
 */
class Search extends \Magento\Backend\App\Action
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry = null;

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected $pandaHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Licentia\Reports\Model\SearchFactory
     */
    protected $searchFactory;

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
