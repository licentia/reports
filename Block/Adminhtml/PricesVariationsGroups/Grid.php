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

namespace Licentia\Reports\Block\Adminhtml\PricesVariationsGroups;

/**
 * Class Grid
 *
 * @package Licentia\Reports\Block\Adminhtml\PricesVariationsGroups
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Licentia\Reports\Model\ResourceModel\PricesVariationsGroups\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    protected $pageLayoutBuilder;

    /**
     * @param \Magento\Backend\Block\Template\Context                                        $context
     * @param \Magento\Backend\Helper\Data                                                   $backendHelper
     * @param \Licentia\Reports\Model\ResourceModel\PricesVariationsGroups\CollectionFactory $collectionFactory
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface               $pageLayoutBuilder
     * @param array                                                                          $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Licentia\Reports\Model\ResourceModel\PricesVariationsGroups\CollectionFactory $collectionFactory,
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        array $data = []
    ) {

        $this->collectionFactory = $collectionFactory;
        $this->pageLayoutBuilder = $pageLayoutBuilder;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Row click url
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {

        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    protected function _construct()
    {

        parent::_construct();
        $this->setId('pandaPricesVariationsGroupsGrid');
        $this->setDefaultSort('item_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {

        $collection = $this->collectionFactory->create();
        /* @var  \Licentia\Reports\Model\ResourceModel\PricesVariationsGroups\Collection $collection */
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {

        $this->addColumn('item_id',
            [
                'header' => __('ID'),
                'index'  => 'item_id',
            ]);

        $this->addColumn('name',
            [
                'header' => __('Name'),
                'index'  => 'name',
            ]);

        $this->addColumn('groups',
            [
                'header'         => __('Customer groups'),
                'index'          => 'groups',
                'filter'         => false,
                'sortable'       => false,
                'frame_callback' => [$this, 'groupResult'],
            ]);

        return parent::_prepareColumns();
    }

    /**
     * @param $value
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function groupResult($value)
    {

        /** @var \Licentia\Reports\Model\ResourceModel\PricesVariationsGroups\Collection $resource */
        $resource = $this->collectionFactory->create()->getResource();
        $connection = $resource->getConnection();
        $value = explode(',', $value);

        $result = '';
        foreach ($value as $groupId) {

            $result .= $connection->fetchOne(
                    $connection->select()
                               ->from($resource->getTable('customer_group'), ['customer_group_code'])
                               ->where('customer_group_id=?', $groupId)
                ) . ' <br>';
        }

        return $result;
    }

}
