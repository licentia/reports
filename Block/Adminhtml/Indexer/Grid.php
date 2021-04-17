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
                'header'   => __('Indexer ID'),
                'index'    => 'indexer_id',
                'sortable' => false,
            ]
        );
        $this->addColumn(
            'desc',
            [
                'header'         => __('Description'),
                'index'          => 'indexer_id',
                'sortable'       => false,
                'frame_callback' => [$this, 'descResult'],
                'is_system'      => true,
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
        /*
              $this->addColumn(
                  'entity_type',
                  [
                      'header'   => __('Entity Type'),
                      'index'    => 'entity_type',
                      'sortable' => false,
                  ]
              );

                      $this->addColumn(
                          'last_entity_id',
                          [
                              'header'   => __('Last Entity ID'),
                              'index'    => 'last_entity_id',
                              'sortable' => false,
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
              */
        $this->addColumn(
            'status',
            [
                'header'         => __('Status'),
                'index'          => 'status',
                'sortable'       => false,
                'frame_callback' => [$this, 'serviceResult'],
            ]
        );

        $this->addColumn(
            'info',
            [
                'header'         => __('Info'),
                'width'          => '50px',
                'index'          => 'indexer_id',
                'sortable'       => false,
                'frame_callback' => [$this, 'infoResult'],
                'is_system'      => true,
            ]
        );

        $this->addColumn(
            'rebuild',
            [
                'header'         => __('Command line rebuild code (in your doc root)'),
                'width'          => '50px',
                'index'          => 'indexer_id',
                'sortable'       => false,
                'frame_callback' => [$this, 'commandResult'],
                'is_system'      => true,
            ]
        );

        $this->addColumn(
            'customer_id',
            [
                'header'         => __('Reindex'),
                'align'          => 'center',
                'width'          => '50px',
                'index'          => 'indexer_id',
                'sortable'       => false,
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
     * @param $row
     *
     * @return string
     */
    public function infoResult($value, $row)
    {

        switch ($value) {
            case 'equity':
                return __('Real-time update / manually update');
                break;
            case 'sales':
                return __('Daily update @3:40am');
                break;
            case 'reorders':
                return __('Daily update @2:20am');
                break;
            case 'search_history':
                return __('Daily update @4:20am');
                break;
            case 'relations':
            case 'performance':
                return __('Daily udate for previous day @1:30am<br>FULL rebuild every monday @1:30am');
                break;
            case 'recommendations':
                return __('Daily udate for previous day @1:30am<br>FULL rebuild every monday @6:00am');
                break;
            case 'search_performance':
                return __('FULL rebuild every monday @2:20am');
                break;
            case 'venn':
                return __('FULL rebuild every monday @3:20am');
                break;
            case 'segments':
                return __('Per-Segment option / manually update');
                break;

            default:
                return '';
        }

        return $value;
    }

    /**
     * @param $value
     * @param $row
     *
     * @return string
     */
    public function commandResult($value, $row)
    {

        return "php bin/magento panda:rebuild " . $value;
    }

    /**
     * @param $value
     * @param $row
     *
     * @return string
     */
    public function descResult($value, $row)
    {

        switch ($value) {
            case 'equity':
                return __('Customer equity values (number of orders, amounts, etc.)');
                break;
            case 'sales':
                return __('Sales analytics');
                break;
            case 'reorders':
                return __('Expected Reorders');
                break;
            case 'search_history':
                return __('Search History Data');
                break;
            case 'relations':
                return __('Product metadata for Recommendations');
                break;
            case 'performance':
                return __('Product Sales Performance');
                break;
            case 'recommendations':
                return __('Product Recommendation Metadata');
                break;
            case 'search_performance':
                return __('Search metadata');
                break;
            case 'venn':
                return __('Venn Analytics');
                break;
            case 'segments':
                return __('Customer Segments');
                break;

            default:
                return '';
        }

        return $value;
    }

    /**
     * @param $value
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function customerResult($value)
    {

        $url = $this->getUrl('*/*/reindex', ['id' => $value]);

        return '<a href="' . $url . '">' . __('Full Rebuild using CRON') . '</a>';
    }
}
