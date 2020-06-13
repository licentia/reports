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

namespace Licentia\Reports\Model;

use Licentia\Reports\Api\Data\PricesVariationsInterfaceFactory;
use Licentia\Reports\Api\Data\PricesVariationsSearchResultsInterfaceFactory;
use Licentia\Reports\Model\ResourceModel\Sales\PricesVariation\CollectionFactory as PricesVariationsCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class KpisRepository
 *
 * @package Licentia\Panda\Model
 */
class PricesVariationsRepository implements \Licentia\Reports\Api\PricesVariationsRepositoryInterface
{

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var
     */
    protected $KpisFactory;

    /**
     * @var PricesVariationsSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var PricesVariationsInterfaceFactory
     */
    protected $dataKpisFactory;

    /**
     * @var PricesVariationsCollectionFactory
     */
    protected $pricesVariationsCollectionFactory;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var
     */
    protected $PricesVariationsCollectionFactory;

    /**
     * @param PricesVariationsInterfaceFactory              $dataKpisFactory
     * @param PricesVariationsCollectionFactory             $kpisCollectionFactory
     * @param PricesVariationsSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper                              $dataObjectHelper
     * @param DataObjectProcessor                           $dataObjectProcessor
     */
    public function __construct(
        PricesVariationsInterfaceFactory $dataKpisFactory,
        PricesVariationsCollectionFactory $kpisCollectionFactory,
        PricesVariationsSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {

        $this->pricesVariationsCollectionFactory = $kpisCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataKpisFactory = $dataKpisFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * returns list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     *
     * @return \Licentia\Equity\Api\Data\KpisSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->pricesVariationsCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $items = [];

        foreach ($collection as $kpisModel) {
            $kpisData = $this->dataKpisFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $kpisData,
                $kpisModel->getData(),
                \Licentia\Reports\Api\Data\PricesVariationsInterface::class
            );
            $items[] = $this->dataObjectProcessor->buildOutputDataArray(
                $kpisData,
                \Licentia\Reports\Api\Data\PricesVariationsInterface::class
            );
        }
        $searchResults->setItems($items);

        return $searchResults;
    }
}
