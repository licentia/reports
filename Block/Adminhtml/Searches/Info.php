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
 * Class Info
 *
 * @package Licentia\Panda\Block\Adminhtml\Stats
 */
class Info extends \Magento\Backend\Block\Template
{

    /**
     * @var \Licentia\Reports\Model\Sales\StatsFactory
     */
    protected $statsFactory;

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
    protected $collectionPeriods;

    /**
     * @var \Licentia\Equity\Model\SegmentsFactory
     */
    protected $segmentsFactory;

    /**
     * View constructor.
     *
     * @param \Licentia\Equity\Model\SegmentsFactory     $segmentsFactory
     * @param \Licentia\Reports\Model\Sales\StatsFactory $statsFactory
     * @param \Magento\Catalog\Model\ProductFactory      $productFactory
     * @param \Magento\Backend\Block\Template\Context    $context
     * @param array                                      $data
     */
    public function __construct(
        \Licentia\Equity\Model\SegmentsFactory $segmentsFactory,
        \Licentia\Reports\Model\Sales\StatsFactory $statsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {

        parent::__construct($context, $data);

        $this->statsFactory = $statsFactory;
        $this->productFactory = $productFactory;
        $this->segmentsFactory = $segmentsFactory;

        $this->setTemplate('Licentia_Reports::searches/info.phtml');
    }

    /**
     * @return array
     */
    public function getGroups()
    {

        $blocks = \Licentia\Reports\Model\Search\Stats::getGroups();
        $blocks['single'] = 'Query Totals';

        return $blocks;
    }

    /**
     * @return string
     */
    public function getGroup()
    {

        $type = strtolower(
            $this->getRequest()->getParam('group_results', 'date')
        );

        if (!in_array($type, array_keys($this->getGroups()))) {
            $type = 'month';
        }

        return $type;
    }

    /**
     * @return array
     */
    public function getTypes()
    {

        return $this->statsFactory->create()->getTypes();
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
     * @return mixed
     */
    public function getSegmentId()
    {

        return $this->getRequest()->getParam('segment_id');
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
}
