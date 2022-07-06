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

use Licentia\Reports\Model\Products\Relations;
use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Api\FilterBuilder;
use \Magento\Catalog\Model\Product\Visibility;

/**
 * Class Recommendations
 *
 * @package Licentia\Panda\Model
 */
class Recommendations extends \Magento\Framework\Model\AbstractModel
    implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'panda_recommendations';

    /**
     * @var SearchCriteriaInterface
     */
    protected $_searchCriteria;

    /**
     * @var
     */
    protected $filterGroup;

    /**
     * @var $instances
     */
    protected $instances = [];

    /**
     * @var $instancesById
     */
    protected $instancesById = [];

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollection;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quoteCollection;

    /**
     * @var array
     */
    protected $productIds = [];

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollection;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Event\CollectionFactory
     */
    protected $eventsCollection;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var \Magento\Catalog\Model\Product\VisibilityFactory
     */
    protected $visibility;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $configAttributes;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Product\CollectionFactory
     */
    protected $soldCollection;

    /**
     * @var Products\RelationsFactory
     */
    protected $relationsFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected $pandaHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Recommendations constructor.
     *
     * @param \Magento\Framework\App\RequestInterface                        $request
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                   $searchCriteriaBuilder
     * @param FilterBuilder                                                  $filterBuilder
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder               $filterGroupBuilder
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface           $timezone
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory     $quoteCollection
     * @param \Licentia\Reports\Helper\Data                                  $newsletterData
     * @param Products\RelationsFactory                                      $relationsFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                $productRepository
     * @param Visibility                                                     $visibilityFactory
     * @param \Magento\Catalog\Model\CategoryFactory                         $categoryFactory
     * @param \Magento\Catalog\Model\Config                                  $configAttributes
     * @param \Magento\Wishlist\Model\WishlistFactory                        $wishlistFactory
     * @param \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $soldCollection
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory     $orderCollection
     * @param \Magento\Reports\Model\ResourceModel\Event\CollectionFactory   $eventsCollection
     * @param \Magento\Store\Model\StoreManagerInterface                     $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
     * @param \Magento\Framework\Model\Context                               $context
     * @param \Magento\Framework\Registry                                    $registry
     * @param \Licentia\Reports\Helper\Data                                  $pandaHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null   $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null             $resourceCollection
     * @param array                                                          $data
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollection,
        \Licentia\Reports\Helper\Data $newsletterData,
        \Licentia\Reports\Model\Products\RelationsFactory $relationsFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Visibility $visibilityFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Config $configAttributes,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $soldCollection,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Reports\Model\ResourceModel\Event\CollectionFactory $eventsCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Licentia\Reports\Helper\Data $pandaHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroup = $filterGroupBuilder;

        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->pandaHelper = $pandaHelper;
        $this->relationsFactory = $relationsFactory;
        $this->soldCollection = $soldCollection;
        $this->quoteCollection = $quoteCollection;
        $this->productCollection = $productCollection;
        $this->helperData = $newsletterData;
        $this->productRepository = $productRepository;
        $this->orderCollection = $orderCollection;
        $this->eventsCollection = $eventsCollection;
        $this->wishlistFactory = $wishlistFactory;
        $this->configAttributes = $configAttributes;
        $this->visibility = $visibilityFactory;
        $this->timezone = $timezone;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_init(ResourceModel\Recommendations::class);

    }

    /**
     * @param      $code
     *
     * @return $this
     */
    public function loadFromCode($code)
    {

        $collection = $this->getActiveCollection();
        $collection->addFieldToFilter('code', $code);

        $collection->setPageSize(1);
        $collection->getSelect()->order('RAND()');

        $this->setData($collection->getFirstItem()->getData());

        return $this;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getActiveCollection()
    {

        $now = $this->pandaHelper->gmtDate();

        $collection = $this->getCollection()
                           ->addFieldToFilter('is_active', 1)
                           ->addFieldToFilter('from_date', [['null' => true], ['lteq' => $now]])
                           ->addFieldToFilter('to_date', [['null' => true], ['gteq' => $now]]);

        return $collection;
    }

    /**
     * @return bool|int
     */
    public function getCustomerId()
    {

        return $this->helperData->getCustomerId();
    }

    /**
     * @return bool|string
     */
    public function getCustomerEmail()
    {

        return $this->helperData->getCustomerEmail();
    }

    /**
     * @param bool $idsOnly
     *
     * @return array|bool|\Magento\Catalog\Model\ResourceModel\Product\Collection|string
     */
    public function getRecommendationsCollection($idsOnly = false)
    {

        $store = $this->storeManager->getStore();

        if (is_null($store->getId())) {

            $storeId = $this->storeManager->getStore($store->getCode());
            $this->storeManager->getStore()->setId($storeId->getId());

        }

        $cacheIdentifier = sha1(json_encode($this->getData())) .
                           $this->storeManager->getStore()->getId() .
                           implode(',', (array) $this->request->getParam('skus')) .
                           $this->getCustomerEmail() . (int) $idsOnly;

        $segment = trim($this->getEntityType());
        $productsIds = [];

        if ($this->getData('widget_cache') && $cache = $this->_cacheManager->load($cacheIdentifier)) {

            $productsIds = json_decode($cache, true);

        } else {

            switch ($segment) {
                case 'attributes':
                    $productsIds = $this->getAttributesProducts();
                    break;
                case 'related_order':
                    $productsIds = $this->getRelatedProductsFromLastOrder();
                    break;
                case 'related':
                    $productsIds = $this->getRelatedProducts();
                    break;
                case 'abandoned':
                    $productsIds = $this->getAbandonedCart();
                    break;
                case 'views':
                    $productsIds = $this->getViewsProducts();
                    break;
                case 'wishlist':
                    $productsIds = $this->getWishlistProducts();
                    break;
                case 'category':
                    $productsIds = $this->getCategoriesProducts();
                    break;
                case 'engine':
                    $productsIds = $this->getEngineProducts();
                    break;
                case 'recent':
                    $productsIds = $this->getRecentProducts();
                    break;
            }

            if (count($productsIds) == 0) {
                $segment = $this->getIfFail();

                switch ($segment) {
                    case 'views':
                        $productsIds = $this->getViewsProducts();
                        break;
                    case 'category':
                        $productsIds = $this->getCategoriesProducts(true);
                        break;
                    case 'recent':
                        $productsIds = $this->getRecentProducts();
                        break;
                    default:
                        $productsIds = [];
                        break;
                }
            }

            $this->_cacheManager->save(json_encode($productsIds), $cacheIdentifier, ['panda_recommendations']);
        }

        if ($idsOnly) {

            $filters[] = $this->filterBuilder->setField('entity_id')
                                             ->setValue($productsIds)
                                             ->setConditionType('in')
                                             ->create();
            $this->searchCriteriaBuilder->addFilters($filters);

            $searchCriteria = $this->searchCriteriaBuilder->create();
            $searchResults = $this->productRepository->getList($searchCriteria);

            return $searchResults;

        }

        $collection = $this->productCollection->create()
                                              ->addAttributeToFilter('entity_id', ['in' => $productsIds])
                                              ->setVisibility($this->visibility->getVisibleInCatalogIds())
                                              ->addMinimalPrice()
                                              ->addFinalPrice()
                                              ->addTaxPercents()
                                              ->addAttributeToSelect($this->configAttributes->getProductAttributes())
                                              ->addUrlRewrite()
                                              ->addStoreFilter()
                                              ->setPage(1, $this->getNumberProducts());
        switch ($this->getSortResults()) {
            case 'random':
                $collection->getSelect()->order('rand()');
                break;
            case 'created_at':
                $collection->addAttributeToSort('created_at', 'DESC');
                break;
            case 'price_asc':
                $collection->addAttributeToSort('price', 'ASC');
                break;
            case 'price_desc':
            default:
                $collection->addAttributeToSort('price', 'DESC');
                break;
        }

        return $collection;
    }

    /**
     * @return array
     */
    public function getRecommendationsApi()
    {

        return $this->getRecommendationsCollection(true);

    }

    /**
     * @return array
     */
    public function getWishlistProducts()
    {

        $customerId = $this->getCustomerId();
        if (!$customerId) {
            return [];
        }

        if (isset($this->productIds['wishlist'])) {
            return $this->productIds['wishlist'];
        }

        $wishlist = $this->wishlistFactory->create()
                                          ->loadByCustomerId($customerId)
                                          ->getItemCollection()->setOrder('added_at', 'desc');

        $productsIds = [];

        /** @var \Magento\Wishlist\Model\Item $item */
        foreach ($wishlist as $item) {
            $productsIds[] = $item->getProductId();
        }

        return $productsIds;

    }

    /**
     * @param bool $fail
     *
     * @return array
     */
    public function getCategoriesProducts($fail = false)
    {

        $productsIds = [];

        if ($fail) {
            $categoryId = $this->getCategoryFail();
        } else {

            $categoryId = $this->getCategory();
        }

        $cat = $this->categoryFactory->create()->load($categoryId);

        $collection = $this->productCollection->create();
        $collection->addCategoryFilter($cat);
        $collection->distinct(true);
        $collection->addAttributeToSort('price', 'desc');
        $collection->setPageSize($this->getNumberProducts() * 3);

        foreach ($collection as $product) {
            $productsIds[] = $product->getId();
        }

        return $productsIds;

    }

    /**
     * @return array|bool
     */
    public function getAttributesProducts()
    {

        $customerId = $this->getCustomerId();

        if (!$customerId) {
            return false;
        }

        $productsIds = [];

        if ($customerId) {

            $table = 'panda_segments_metadata_attrs';
            $select = $this->getConnection()
                           ->select()
                           ->from($this->getTable($table), ['attribute_id'])
                           ->where('customer_id=?', $customerId)
                           ->limit('1');

            $attributeId = $this->getConnection()->fetchOne($select);

            if (!$attributeId) {
                return [];
            }
        } else {

            $items = $this->getViewsProducts();

            $products = $this->productCollection->create()
                                                ->addAttributeToSort('price', 'desc')
                                                ->setPageSize($this->getNumberProducts() * 3)
                                                ->addAttributeToFilter('entity_id', ['in' => $items]);

            $productsIds = [];

            $attrs = [];

            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($products as $product) {

                $attributes = $product->getAttributes();
                foreach ($attributes as $attribute) {
                    if ($attribute->getData('is_filterable')) {
                        if (!isset($attrs[$attribute->getName()])) {
                            $attrs[$attribute->getName()] = 1;
                        } else {
                            $attrs[$attribute->getName()] = $attrs[$attribute->getName()] + 1;
                        }
                    }
                }
            }

            ksort($attrs);
            $attr = array_keys($attrs);

            if (count($attr) == 0) {
                return [];
            }
            $attributeId = $attr[0];
        }

        $catalog = $this->productCollection->create()->addAttributeToFilter($attributeId, ['neq' => 'panda']);

        $catalog->setPageSize($this->getNumberProducts() * 3);

        /** @var \Magento\Catalog\Model\Product $prod */
        foreach ($catalog as $prod) {
            $productsIds[$prod->getId()] = $prod->getId();
        }

        return $productsIds;

    }

    /**
     * @return array|bool
     */
    public function getRelatedProductsFromLastOrder()
    {

        $customerId = $this->getCustomerId();
        $customerEmail = $this->getCustomerEmail();

        if (!$customerEmail && !$customerEmail) {
            return false;
        }

        $orders = $this->orderCollection->create()
                                        ->addAttributeToSelect('entity_id')
                                        ->addAttributeToFilter('state', 'complete')
                                        ->setOrder('created_at', 'DESC')
                                        ->setPageSize(1);

        if ($customerId) {
            $orders->addAttributeToFilter('customer_id', $customerId);
        } else {
            $orders->addAttributeToFilter('customer_email', $customerEmail);
        }

        $productsIds = [];

        /** @var \Magento\Sales\Model\Order $orderObject */
        $orderObject = $orders->getFirstItem();

        $items = $orderObject->getAllVisibleItems();

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($items as $item) {

            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById($item->getProductId());

            if (!$product->getId()) {
                continue;
            }

            $rp = $product->getRelatedProductIds();
            foreach ($rp as $value) {
                $productsIds[$value] = $value;
            }
        }

        return $productsIds;
    }

    /**
     * @return array|bool
     */
    public function getRelatedProducts()
    {

        $customerId = $this->getCustomerId();
        $customerEmail = $this->getCustomerEmail();

        if (!$customerEmail && !$customerEmail) {
            return false;
        }

        $orders = $this->orderCollection->create()
                                        ->addAttributeToSelect('entity_id')
                                        ->addAttributeToFilter('state', 'complete')
                                        ->setPageSize(5);

        if ($customerId) {
            $orders->addAttributeToFilter('customer_id', $customerId);
        } else {
            $orders->addAttributeToFilter('customer_email', $customerEmail);
        }

        $productsIds = [];

        /** @var \Magento\Sales\Model\order $order */
        foreach ($orders as $order) {
            $items = $order->getItemsCollection();

            foreach ($items as $item) {

                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->productRepository->getById($item->getProductId());

                if (!$product->getId()) {
                    continue;
                }

                $rp = $product->getRelatedProductIds();
                foreach ($rp as $value) {
                    $productsIds[$value] = $value;
                }
            }
        }

        return $productsIds;

    }

    /**
     * @return array|bool
     */
    public function getAbandonedCart()
    {

        $customerId = $this->getCustomerId();
        $customerEmail = $this->getCustomerEmail();

        if (!$customerEmail && !$customerEmail) {
            return false;
        }

        $orders = $this->quoteCollection->create()
                                        ->addFieldToSelect('*')
                                        ->addFieldToFilter(
                                            'store_id',
                                            $this->storeManager->getStore()
                                                               ->getId()
                                        )
                                        ->addFieldToFilter('items_count', ['neq' => '0'])
                                        ->addFieldToFilter('is_active', '1')
                                        ->setOrder('updated_at', 'DESC');

        if ($customerEmail) {
            $orders->addFieldToFilter('customer_email', $customerEmail);
        } else {
            $orders->addFieldToFilter('customer_id', $customerId);
        }
        $orders->setPageSize(1);

        $productsIds = [];

        /** @var \Magento\Sales\Model\order $order */
        foreach ($orders as $order) {
            $items = $order->getItemsCollection();

            foreach ($items as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }
                $productsIds[$item->getProductId()] = $item->getProductId();
            }
        }

        return $productsIds;

    }

    /**
     * @return array
     */
    public function getRecentProducts()
    {

        $todayDate = $this->helperData->gmtDate();

        $collection = $this->productCollection->create();
        $collection->setVisibility($this->visibility->getVisibleInSiteIds());

        $collection->addAttributeToFilter(
            'news_from_date',
            [
                'or' => [
                    0 => ['date' => true, 'to' => $todayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ],
            ],
            'left'
        )
                   ->addAttributeToFilter(
                       'news_to_date',
                       [
                           'or' => [
                               0 => ['date' => true, 'from' => $todayDate],
                               1 => ['is' => new \Zend_Db_Expr('null')],
                           ],
                       ],
                       'left'
                   )
                   ->addAttributeToSort('news_from_date', 'desc')
                   ->setPageSize($this->getNumberProducts() * 3);

        $productsIds = [];

        foreach ($collection as $value) {
            $productsIds[] = $value->getId();
        }

        return $productsIds;

    }

    /**
     * @return array
     */
    public function getViewsProducts()
    {

        $productsIds = [];

        $customerId = $this->getCustomerId();

        if ($customerId) {

            $table = 'panda_segments_metadata_products';
            $select = $this->getConnection()
                           ->select()
                           ->from($this->getTable($table), ['product_id'])
                           ->where('customer_id=?', $customerId);

            $result = $this->getConnection()->fetchCol($select);

            $productsIds = array_combine($result, $result);

            if ($productsIds) {

                return (array) $productsIds;
            }
        }

        $storeId = $this->storeManager->getStore()->getId();
        $products = $this->soldCollection->create()
                                         ->addAttributeToSelect('entity_id')
                                         ->setStoreId($storeId)
                                         ->addStoreFilter($storeId)
                                         ->addViewsCount()
                                         ->setPageSize($this->getNumberProducts() * 3);

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($products as $product) {
            $productsIds[$product->getEntityId()] = $product->getEntityId();
        }

        return (array) $productsIds;

    }

    /**
     * @return array
     */
    public function getEngineProducts()
    {

        $connection = $this->getConnection();

        $customerEmail = $this->getCustomerEmail();

        $kpi = $this->getConnection()
                    ->fetchRow(
                        $this->getConnection()
                             ->select()
                             ->from($this->getTable('panda_customers_kpis'))
                             ->where('email_meta=?', $customerEmail)
                    );

        $drill = 'global';
        $country = '';
        $region = '';

        if ($this->getSegmentDrill() == 'age' && $kpi['age'] >= 18) {
            $drill = 'age';
        }

        $segmentIds = false;

        if ($this->getUseSegments() == 1) {

            $segmentIds = $connection->fetchCol(
                $connection->select()
                           ->from($this->getTable('panda_segments_records'))
                           ->where('email=?', $customerEmail)
            );

        }

        if ($this->getSegmentDrill() == 'region') {

            $region = $connection->fetchOne(
                $connection->select()
                           ->from($this->getTable('sales_order_address'), ['region'])
                           ->where('email=?', $customerEmail)
                           ->group('country_id')
                           ->order('COUNT(*) DESC')
                           ->limit(1)
            );

            if ($region) {
                $drill = 'region';
            } else {
                $this->setData('segment_drill', 'country');
            }

        }

        if ($this->getSegmentDrill() == 'country') {

            $country = $connection->fetchOne(
                $connection->select()
                           ->from($this->getTable('sales_order_address'), ['country_id'])
                           ->where('email=?', $customerEmail)
                           ->group('country_id')
                           ->order('COUNT(*) DESC')
                           ->limit(1)
            );

            $drill = 'countries';

        }

        if ($this->getSegmentDrill() == 'gender' && in_array($kpi['gender'], ['male', 'female'])) {
            $drill = $kpi['gender'];
        }

        $sql = $connection->select();

        if ($drill == 'countries') {
            $sql->where('country = ?', $country);
        }

        if ($drill == 'region') {
            $sql->where('region = ?', $region);
        }

        if ($drill == 'age') {

            $ranges = Relations::POSSIBLE_AGE_RANGES;
            $ageRange = false;
            foreach ($ranges as $range) {

                $tmpRange = explode('-', $range);

                if ($kpi['age'] >= $tmpRange[0] && (!isset($tmpRange[1]) || $kpi['age'] <= $tmpRange[1])) {
                    $ageRange = $range;
                    break;
                }
            }

            if ($ageRange) {
                $sql->where('age = ?', $ageRange);
            } else {
                $drill = 'global';
            }
        }

        $table = $this->getTable(Products\Relations::PRODUCTS_RECOMMENDATIONS_TABLE_PREFIX . $drill);

        $sql->from($table);

        $skus = [];
        if ($this->getBasedOn() == 'specific_product') {
            $skus = str_getcsv($this->getSkus());

            if ((array) $this->request->getParam('skus')) {
                $skus = (array) $this->request->getParam('skus');
            }

            $skus = array_map('trim', $skus);
        }

        if ($this->getBasedOn() == 'purchase_history') {

            $sqlAux = clone $connection->select();
            $skus = (array) $connection->fetchCol(
                $sqlAux->reset()
                       ->from($this->getTable('sales_order'), [])
                       ->join(
                           $this->getTable('sales_order_item'),
                           $this->getTable('sales_order_item') . '.order_id=' . $this->getTable(
                               'sales_order'
                           ) . '.entity_id',
                           ['sku']
                       )
                       ->where('customer_email=?', $customerEmail)
                       ->group('sku')
                       ->order('COUNT(*) DESC')
                       ->limit(25)
            );
        }

        if ($this->getBasedOn() == 'current_product') {

            $sku = $this->_registry->registry('product');

            if (!$sku) {
                $this->_registry->registry('current_product');
            }
            $skus = [];
            if ($sku) {
                $skus = (array) $sku->getSku();
            }
        }

        if (count($skus) == 0) {
            return [];
        }

        if ($segmentIds) {
            $sql->where('segment_id IN (?)', $segmentIds);
        }

        if ($this->getLevel() == 'after_order') {
            $columns = [];

            foreach (range(1, Relations::NUMBER_PRODUCTS_RECOMMENDATION_AFTER_PURCHASE)
                     as $number) {
                $columns[] = 'after_order_' . $number;
                $columns[] = 'after_order_total_' . $number;
            }
        } elseif ($this->getLevel() == 'second_level') {
            $columns = [];

            foreach (range(1, 3) as $level) {
                foreach (range(1, Relations::NUMBER_PRODUCTS_RECOMMENDATION_MAIN) as
                         $number) {
                    $columns[] = 'related_main_' . $level . '_' . $number;
                    $columns[] = 'related_main_total_' . $number;
                }
            }

        } else {
            $columns = [];

            foreach (range(1, Relations::NUMBER_PRODUCTS_RECOMMENDATION) as $number) {
                $columns[] = 'related_' . $number;
                $columns[] = 'related_total_' . $number;
            }
        }

        $sql->where('sku IN (?)', $skus);

        $sql->reset('columns')
            ->columns($columns);

        $skusFinal = $connection->fetchAll($sql);

        foreach ($skusFinal as $key => $row) {
            $skusFinal[$key] = array_filter($skusFinal[$key]);
        }

        $theSku = [];
        foreach ($skusFinal as $skus) {
            foreach ($skus as $key => $sku) {
                if (stripos($key, 'total_') === false) {
                    $theSku[] = $sku;
                }
            }
        }

        $theSku = array_unique($theSku);

        $productsIds = $connection->select()
                                  ->from($this->getTable('catalog_product_entity'), ['entity_id'])
                                  ->where('sku IN (?)', $theSku);

        return (array) $connection->fetchCol($productsIds);
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection()
    {

        $resource = $this->orderCollection->create()->getResource();

        return $resource->getConnection();
    }

    /**
     * @param $table
     *
     * @return mixed
     */
    public function getTable($table)
    {

        return $this->orderCollection->create()
                                     ->getResource()->getTable($table);
    }

    /**
     * @return array
     */
    public static function getRecommendationsTypes()
    {

        return [
            'attributes'    => __('Product with common Attributes'),
            'related_order' => __('Related Products From Last Completed Order'),
            'related'       => __('Related Products From All Previous Completed Orders'),
            'abandoned'     => __('Products in the Shopping Cart'),
            'category'      => __('Products in a Category'),
            'wishlist'      => __('Products in the Customer Wish List'),
            'views'         => __('Most Viewed Products'),
            'recent'        => __('New Products'),
            'engine'        => __('Recommendation Engine'),
        ];

    }

    /**
     * @return array
     */
    public function toOptionArray()
    {

        $return = [];

        foreach (self::getRecommendationsTypes() as $key => $value) {
            $return[] = ['value' => $key, 'label' => $value];
        }

        return $return;
    }

    /**
     * @return array
     */
    /**
     * @return array
     */
    public function getOptionArray()
    {

        return $this->getRecommendationsTypes();
    }

    /**
     * @param $recommendationId
     *
     * @return $this
     */
    public function setRecommendationId($recommendationId)
    {

        return $this->setData('recommendation_id', $recommendationId);
    }

    /**
     * @param $internalName
     *
     * @return $this
     */
    public function setInternalName($internalName)
    {

        return $this->setData('internal_name', $internalName);
    }

    /**
     * @param $title
     *
     * @return $this
     */
    public function setTitle($title)
    {

        return $this->setData('title', $title);
    }

    /**
     * @param $code
     *
     * @return $this
     */
    public function setCode($code)
    {

        return $this->setData('code', $code);
    }

    /**
     * @param $entityType
     *
     * @return $this
     */
    public function setEntityType($entityType)
    {

        return $this->setData('entity_type', $entityType);
    }

    /**
     * @param $category
     *
     * @return $this
     */
    public function setCategory($category)
    {

        return $this->setData('category', $category);
    }

    /**
     * @param $basedOn
     *
     * @return $this
     */
    public function setBasedOn($basedOn)
    {

        return $this->setData('based_on', $basedOn);
    }

    /**
     * @param $level
     *
     * @return $this
     */
    public function setLevel($level)
    {

        return $this->setData('level', $level);
    }

    /**
     * @param $skus
     *
     * @return $this
     */
    public function setSkus($skus)
    {

        return $this->setData('skus', $skus);
    }

    /**
     * @param $useSegments
     *
     * @return $this
     */
    public function setUseSegments($useSegments)
    {

        return $this->setData('use_segments', $useSegments);
    }

    /**
     * @param $segmentDrill
     *
     * @return $this
     */
    public function setSegmentDrill($segmentDrill)
    {

        return $this->setData('segment_drill', $segmentDrill);
    }

    /**
     * @param $numberProducts
     *
     * @return $this
     */
    public function setNumberProducts($numberProducts)
    {

        return $this->setData('number_products', $numberProducts);
    }

    /**
     * @param $sortResults
     *
     * @return $this
     */
    public function setSortResults($sortResults)
    {

        return $this->setData('sort_results', $sortResults);
    }

    /**
     * @param $ifFail
     *
     * @return $this
     */
    public function setIfFail($ifFail)
    {

        return $this->setData('if_fail', $ifFail);
    }

    /**
     * @param $categoryFail
     *
     * @return $this
     */
    public function setCategoryFail($categoryFail)
    {

        return $this->setData('category_fail', $categoryFail);
    }

    /**
     * @param $isActive
     *
     * @return $this
     */
    public function setIsActive($isActive)
    {

        return $this->setData('is_active', $isActive);
    }

    /**
     * @param $fromDate
     *
     * @return $this
     */
    public function setFromDate($fromDate)
    {

        return $this->setData('from_date', $fromDate);
    }

    /**
     * @param $toDate
     *
     * @return $this
     */
    public function setToDate($toDate)
    {

        return $this->setData('to_date', $toDate);
    }

    /**
     * @return mixed
     */
    public function getRecommendationId()
    {

        return $this->getData('recommendation_id');
    }

    /**
     * @return mixed
     */
    public function getInternalName()
    {

        return $this->getData('internal_name');
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {

        return $this->getData('title');
    }

    /**
     * @return mixed
     */
    public function getCode()
    {

        return $this->getData('code');
    }

    /**
     * @return mixed
     */
    public function getEntityType()
    {

        return $this->getData('entity_type');
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {

        return $this->getData('category');
    }

    /**
     * @return mixed
     */
    public function getBasedOn()
    {

        return $this->getData('based_on');
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {

        return $this->getData('level');
    }

    /**
     * @return mixed
     */
    public function getSkus()
    {

        return $this->getData('skus');
    }

    /**
     * @return mixed
     */
    public function getUseSegments()
    {

        return $this->getData('use_segments');
    }

    /**
     * @return mixed
     */
    public function getSegmentDrill()
    {

        return $this->getData('segment_drill');
    }

    /**
     * @return mixed
     */
    public function getNumberProducts()
    {

        return $this->getData('number_products');
    }

    /**
     * @return mixed
     */
    public function getSortResults()
    {

        return $this->getData('sort_results');
    }

    /**
     * @return mixed
     */
    public function getIfFail()
    {

        return $this->getData('if_fail');
    }

    /**
     * @return mixed
     */
    public function getCategoryFail()
    {

        return $this->getData('category_fail');
    }

    /**
     * @return mixed
     */
    public function getIsActive()
    {

        return $this->getData('is_active');
    }

    /**
     * @return mixed
     */
    public function getFromDate()
    {

        return $this->getData('from_date');
    }

    /**
     * @return mixed
     */
    public function getToDate()
    {

        return $this->getData('to_date');
    }
}
