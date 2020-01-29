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

namespace Licentia\Reports\Block\Adminhtml\Indexer;

/**
 * Class Grid
 *
 * @package Licentia\Reports\Block\Adminhtml\Indexer
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Licentia\Reports\Model\ResourceModel\Indexer\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    protected $pageLayoutBuilder;

    /**
     * @param \Magento\Backend\Block\Template\Context                          $context
     * @param \Magento\Backend\Helper\Data                                     $backendHelper
     * @param \Licentia\Reports\Model\ResourceModel\Indexer\CollectionFactory  $collectionFactory
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder
     * @param array                                                            $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Licentia\Reports\Model\ResourceModel\Indexer\CollectionFactory $collectionFactory,
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

        return false;
    }

    protected function _construct()
    {

        parent::_construct();
        $this->setId('pandaIndexerGrid');
        $this->setDefaultSort('indexer_id');
        $this->setDefaultDir('ASC');

        $this->setSaveParametersInSession(true);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {

        $collection = $this->collectionFactory->create();
        /* @var $collection \Licentia\Reports\Model\ResourceModel\Indexer\Collection */
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

        $this->addColumn(
            'indexer_id',
            [
                'header' => __('Indexer ID'),
                'index'  => 'indexer_id',
            ]
        );

        $this->addColumn(
            'updated_at',
            [
                'header' => __('Updated At'),
                'index'  => 'updated_at',
                'type'   => 'datetime',
            ]
        );

        $this->addColumn(
            'entity_type',
            [
                'header' => __('Entity Type'),
                'index'  => 'entity_type',
            ]
        );

        $this->addColumn(
            'last_entity_id',
            [
                'header' => __('Last Entity ID'),
                'index'  => 'last_entity_id',
            ]
        );

        $this->addColumn(
            'last_entity_id_updated_at',
            [
                'header' => __('Last Heart Beat'),
                'index'  => 'last_entity_id_updated_at',
                'type'   => 'datetime',
            ]
        );

        $this->addColumn(
            'status',
            [
                'header'         => __('Status'),
                'index'          => 'status',
                'frame_callback' => [$this, 'serviceResult'],
            ]
        );
        $this->addColumn(
            'customer_id',
            [
                'header'         => __('Reindex'),
                'align'          => 'center',
                'width'          => '50px',
                'index'          => 'indexer_id',
                'frame_callback' => [$this, 'customerResult'],
                'is_system'      => true,
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @param $value
     * @param $row
     *
     * @return string
     */
    public function serviceResult($value, $row)
    {

        if ($value == \Licentia\Reports\Model\Indexer::STATUS_WORKING) {
            return ' <span class="grid-severity-minor"><span>' . $row->getStatus() . '</span></span>';
        }

        if ($value == \Licentia\Reports\Model\Indexer::STATUS_INVALID) {
            return ' <span class="grid-severity-major"><span>' . $row->getStatus() . '</span></span>';
        }

        if ($value == \Licentia\Reports\Model\Indexer::STATUS_VALID) {
            return ' <span class="grid-severity-notice"><span>' . $row->getStatus() . '</span></span>';
        }

        return '';
    }

    /**
     * @param $value
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function customerResult($value)
    {

        $url = $this->getUrl('*/*/reindex', ['id' => $value]);

        return '<a href="' . $url . '">' . __('Force Rebuild') . '</a>';
    }
}
