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

namespace Licentia\Reports\Model;

use Licentia\Reports\Api\Data\RecommendationsInterfaceFactory;
use Licentia\Reports\Api\RecommendationsRepositoryInterface;
use Licentia\Reports\Model\ResourceModel\Recommendations as ResourceRecommendations;
use Licentia\Reports\Model\ResourceModel\Recommendations\CollectionFactory as RecommendationsCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class RecommendationsRepository
 *
 * @package Licentia\Panda\Model
 */
class RecommendationsRepository implements RecommendationsRepositoryInterface
{

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var RecommendationsFactory
     */
    protected $recommendationsFactory;

    /**
     * @var RecommendationsCollectionFactory
     */
    protected $recommendationsCollectionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ResourceRecommendations
     */
    protected $resource;

    /**
     * @var
     */
    protected $RecommendationsCollectionFactory;

    /**
     * @var
     */
    protected $RecommendationsFactory;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var RecommendationsInterfaceFactory
     */
    protected $dataRecommendationsFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * RecommendationsRepository constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param ResourceRecommendations                 $resource
     * @param RecommendationsFactory                  $recommendationsFactory
     * @param RecommendationsInterfaceFactory         $dataRecommendationsFactory
     * @param RecommendationsCollectionFactory        $recommendationsCollectionFactory
     * @param DataObjectHelper                        $dataObjectHelper
     * @param DataObjectProcessor                     $dataObjectProcessor
     * @param StoreManagerInterface                   $storeManager
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        ResourceRecommendations $resource,
        RecommendationsFactory $recommendationsFactory,
        RecommendationsInterfaceFactory $dataRecommendationsFactory,
        RecommendationsCollectionFactory $recommendationsCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {

        $this->request = $request;
        $this->registry = $registry;
        $this->customerFactory = $customerFactory;
        $this->resource = $resource;
        $this->recommendationsFactory = $recommendationsFactory;
        $this->recommendationsCollectionFactory = $recommendationsCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataRecommendationsFactory = $dataRecommendationsFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecommendationsApi($zone, $sku = '', $customerId = null)
    {

        if (null === $customerId && $this->request->getParam('customerId')) {
            $customerId = $this->request->getParam('customerId');
        }

        $customer = $this->customerFactory->create()->load($customerId);

        $this->registry->register('current_customer', $customer);

        return $this->recommendationsFactory->create()
                                            ->loadFromCode($zone)
                                            ->setData('skus', str_getcsv($sku))
                                            ->getRecommendationsApi();
    }
}
