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

namespace Licentia\Reports\Block\Adminhtml\Stats;

/**
 * Class View
 *
 * @package Licentia\Panda\Block\Adminhtml\Stats
 */
class View extends \Magento\Backend\Block\Template
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
     * @var array
     */
    protected $columnsTitles = [
        'item_id'                                => 'ID',
        'age'                                    => 'Age',
        'segment_id'                             => 'Segment ID',
        'attribute_code'                         => 'Attribute Code',
        'attribute'                              => 'Attribute',
        'country'                                => 'Country',
        'date'                                   => 'Date',
        'day'                                    => 'Day',
        'day_year'                               => 'Day of Year',
        'weekday'                                => 'Week Day',
        'gender'                                 => 'Gender',
        'region'                                 => 'Region',
        'year'                                   => 'Year',
        'month'                                  => 'Month',
        'sku'                                    => 'SKU',
        'sale_price'                             => 'Sale Price',
        'sale_price_discount'                    => 'Sale Price W/ Discount',
        'profit'                                 => 'Profit',
        'cost'                                   => 'Cost',
        'taxes'                                  => 'Taxes',
        'qty_discount'                           => 'Qty W/ Discount',
        'qty_global'                             => 'Global Qty',
        'qty'                                    => 'Qty',
        'row_total'                              => 'Row AMT',
        'row_total_discount'                     => 'Row AMT W/ Discount',
        'row_total_global'                       => 'Row AMT Global',
        'row_total_discount_percentage'          => 'Row AMT Discount %',
        'unit_price'                             => 'Unit Price',
        'previous_sale_price'                    => 'Previous Sale Price',
        'previous_sale_price_discount'           => 'Previous Sale Price W/ Discount',
        'previous_qty'                           => 'Previous Qty',
        'previous_qty_discount'                  => 'Previous Qty W/ Discount',
        'previous_qty_global'                    => 'Previous Qty Global',
        'previous_row_total'                     => 'Previous Row Total',
        'previous_row_total_discount'            => 'Previous Row Total W/ Discount',
        'previous_row_total_global'              => 'Previous Row Total Global',
        'previous_row_total_discount_percentage' => 'Row Total Discount %',
        'previous_unit_price'                    => 'Previous Unit Price',
        'previous_profit'                        => 'Previous Profit',
        'previous_cost'                          => 'Previous Cost',
        'previous_taxes'                         => 'Previous Taxes',
    ];

    /**
     * @var array
     */
    protected $columnsTitlesAge = ['18-24', '25-34', '35-44', '45-54', '55-64', '65+',];

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var
     */
    protected $skus;

    /**
     * @var
     */
    protected $type;

    /**
     * @var
     */
    protected $possibleAttributes;

    /**
     * @var
     */
    protected $possibleAttributesValues;

    /**
     * @var \Licentia\Reports\Model\Products\RelationsFactory
     */
    protected $relationsFactory;

    /**
     * View constructor.
     *
     * @param \Licentia\Equity\Model\SegmentsFactory            $segmentsFactory
     * @param \Licentia\Reports\Model\Products\RelationsFactory $relationsFactory
     * @param \Magento\Framework\Pricing\Helper\Data            $priceHelper
     * @param \Licentia\Reports\Model\Sales\StatsFactory        $statsFactory
     * @param \Magento\Catalog\Model\ProductFactory             $productFactory
     * @param \Magento\Backend\Block\Template\Context           $context
     * @param array                                             $data
     */
    public function __construct(
        \Licentia\Equity\Model\SegmentsFactory $segmentsFactory,
        \Licentia\Reports\Model\Products\RelationsFactory $relationsFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Licentia\Reports\Model\Sales\StatsFactory $statsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {

        parent::__construct($context, $data);

        $this->priceHelper = $priceHelper;
        $this->statsFactory = $statsFactory;
        $this->productFactory = $productFactory;
        $this->segmentsFactory = $segmentsFactory;
        $this->relationsFactory = $relationsFactory;

        $this->setTemplate('Licentia_Reports::stats/view.phtml');
    }

    /**
     * @return array
     */
    public function getGroups()
    {

        return $this->statsFactory->create()->getGroups();
    }

    /**
     * @return string
     */
    public function getGroup()
    {

        $group = strtolower(
            $this->getRequest()->getParam('group')
        );

        if (!in_array($group, array_keys($this->getGroups()))) {
            $group = 'month';
        }

        return $group;
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
     * @return array
     */
    public function getSKUs()
    {

        if ($this->skus) {
            return $this->skus;
        }

        $return = [];
        if ($this->getType() == 'attribute') {
            $return = [0];
        } else {
            for ($i = 1; $i <= 5; $i++) {
                if ($sku = $this->getRequest()->getParam('sku' . $i)) {
                    if ($this->getProduct($sku)) {
                        $return[$sku] = $sku;
                    }
                }
            }
        }

        $this->skus = $return;

        return $this->skus;
    }

    /**
     * @return mixed
     */
    public function getPeriodsInCollection()
    {

        $sku = $this->getSKUs();

        $type = $this->getType();

        $this->type = $type;

        $group = $this->getGroup();

        if (isset($this->collectionPeriods)) {
            return $this->collectionPeriods;
        }

        $collection = $this->statsFactory->create()->getPeriodsInCollection(
            $sku,
            $type,
            $group,
            $this->getAttribute()
        );

        $this->collectionPeriods = $collection;

        return $this->collectionPeriods;
    }

    /**
     * @return mixed
     */
    public function getStatsArray()
    {

        $sku = $this->getSKUs();

        $type = $this->getType();

        $group = $this->getGroup();

        $this->type = $type;

        if (isset($this->collection)) {
            return $this->collection;
        }

        $intervalStart = $this->getRequest()->getParam('interval_start');
        $intervalEnd = $this->getRequest()->getParam('interval_end');

        $collection = $this->statsFactory->create()
                                         ->getStatsCollection(
                                             $sku,
                                             $type,
                                             $group,
                                             $this->getSegmentId(),
                                             $intervalStart,
                                             $intervalEnd,
                                             $this->getAttribute(),
                                             $this->getAttributeValue(),
                                             $this->getAttribute2()
                                         );

        if (count($collection) == 0 || count(reset($collection)) == 0) {
            return [];
        }

        $this->collection = $collection;

        return $this->collection;
    }

    /**
     * @return array
     */
    public function getDefaultTitlesForTable()
    {

        return [
            'sale_price',
            'qty_global',
            'row_total_global',
            'unit_price',
        ];
    }

    /**
     * @return array
     */
    public function getDefaultTitlesForChart()
    {

        return [
            'qty_global',
            'row_total_global',

        ];
    }

    /**
     * @param string $place
     *
     * @return array
     */
    public function getColumnsTitles($place = 'table')
    {

        $data = $this->getStatsArray();

        if (count($data) == 0 || count(reset($data)) == 0) {
            return [];
        }

        $skus = reset($data);
        $skus = reset($skus);

        $keys = array_keys(reset($skus));

        $fields = [];
        if ($place == 'table') {
            if ($fieldsts = $this->getRequest()->getParam('fieldsts')) {
                $fields = explode(',', $fieldsts);
            } else {
                $fields = $this->getDefaultTitlesForTable();
            }
        }
        if ($place == 'chart') {
            if ($fieldsts = $this->getRequest()->getParam('fieldsts')) {
                $fields = explode(',', $fieldsts);
            } else {
                $fields = $this->getDefaultTitlesForChart();
            }
        }

        $return = [];
        foreach ($keys as $key => $value) {
            if (!in_array($value, $fields)) {
                continue;
            }

            if (stripos($value, 'previous_') !== false) {
                continue;
            }

            $return[$value] = $this->columnsTitles[$value];
        }

        return $return;
    }

    /**
     * @return array
     */
    public function getFieldsInUse()
    {

        return array_keys($this->getColumnsTitles());
    }

    /**
     * @return array
     */
    public function getAvailableFields()
    {

        return \Licentia\Reports\Model\Sales\Stats::AVAILABLE_FIELDS_TO_FILTER;
    }

    /**
     * @return array
     */
    public function getAges()
    {

        if (in_array($this->type, ['age', 'country', 'gender', 'region', 'attribute'])) {
            $data = $this->getStatsArray();
            $skus = reset($data);

            if (is_array($skus)) {
                $skus = reset($skus);
            } else {
                return [];
            }

            return array_keys($skus);
        }

        return [0];
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function formatPeriodColumn($value)
    {

        if ($this->getGroup() == 'month' && is_numeric($value)) {
            $dt = \DateTime::createFromFormat('!m', $value);

            return $dt->format('F');
        }

        if ($this->getGroup() == 'day') {
            $tmp = explode('-', $value);

            if (count($tmp) > 2 && is_numeric($tmp[1]) && is_numeric($tmp[2])) {
                $dt = \DateTime::createFromFormat('!m', $tmp[1]);

                return __($dt->format('F')) . ' ' . $tmp[2];
            }

            return $value;
        }

        if ($this->getGroup() == 'weekday') {
            $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

            return $days[$value];
        }

        if ($this->getGroup() == 'year_month') {
            $tmp = explode('-', $value);

            if (count($tmp) > 1 && is_numeric($tmp[0]) && is_numeric($tmp[1])) {
                $dt = \DateTime::createFromFormat('!m', $tmp[1]);

                return __($dt->format('F')) . ' ' . $tmp[0];
            }

            return $value;
        }

        return $value;
    }

    /**
     * @param        $key
     * @param        $value
     *
     * @return float|string
     */
    public function getRowValue($key, $value)
    {

        if ($value == '-') {
            return $value;
        }

        if ($key == 'month') {
            $dt = \DateTime::createFromFormat('!m', $value);

            return $dt->format('F');
        }

        if ($key == 'country') {
            if ($value == 'UK') {
                $value = 'GB';
            }
            try {
                return \Zend_Locale::getTranslationList('territory')[$value];
            } catch (\Exception $e) {
                return $value;
            }
        }

        if (in_array(
            $key,
            [
                'sale_price',
                'sale_price_discount',
                'row_total',
                'row_total_discount',
                'row_total_global',
                'unit_price',
                'profit',
                'cost',
                'taxes',
            ]
        )) {
            return $this->priceHelper->currency($value, true, false);
        }

        if (in_array($key, ['qty', 'qty_global', 'qty_discount'])) {
            return number_format($value, 2);
        }

        return $value;
    }

    /**
     * @param        $key
     * @param        $collection
     * @param        $ages
     * @param int    $age
     * @param string $default
     *
     * @return string
     */
    public function getBackgroundColor($key, $collection, $ages, $age = 0, $default = '')
    {

        if ($this->getRequest()->getParam('hide_colors') == 1 || $collection[$age][$key] == '-') {
            return $default;
        }

        if (is_numeric($age) && $age == 0) {
            return '#0F4699; color: #FFF";';
        }

        $array = [];

        foreach ($ages as $age1) {
            if ($age1 === 0 || $collection[$age1][$key] == '-') {
                continue;
            }

            $array[$age1] = $collection[$age1][$key];

            asort($array);
        }

        $total = round(max($array) - min($array));

        if ($total == 0) {
            return $default;
        }

        $valueColor = abs(round(($collection[$age][$key] - min($array)) * 100 / $total));

        return '#' . $this->percent2Color($valueColor);
    }

    /**
     * @param        $key
     * @param        $collection
     * @param string $skuC
     * @param string $default
     * @param string $day
     * @param array  $skus
     *
     * @return string
     */
    public function getBackgroundColorProducts($key, $collection, $skuC = '', $default = '', $day = '', $skus = [])
    {

        if (!is_array($skus)) {
            return $default;
        }

        $array = [];
        $total = 0;
        foreach ($skus as $sku) {
            $total += (int) $collection[$sku][$day][0][$key];
            $array[] = $collection[$sku][$day][0][$key];
        }

        if ($total == 0) {
            return $default;
        }

        $valueColor = abs(round((int) ($collection[$skuC][$day][0][$key]) * 100 / $total));

        return '#' . $this->percent2Color($valueColor);
    }

    /**
     * @param        $value
     * @param int    $brightness
     * @param int    $max
     * @param string $thirdColorHex
     *
     * @return string
     */
    public function percent2Color($value, $brightness = 255, $max = 100, $thirdColorHex = '00')
    {

        if ($value >= $max) {
            return "008000; color: #FFF";
        }

        $first = (1 - ($value / $max)) * $brightness;
        $second = ($value / $max) * $brightness;

        // Find the influence of the middle color (yellow if 1st and 2nd are red and green)
        $diff = abs($first - $second);
        $influence = ($brightness - $diff) / 2;
        $first = (int) $first + $influence;
        $second = (int) $second + $influence;

        // Convert to HEX, format and return
        $firstHex = str_pad(dechex($first), 2, 0, STR_PAD_LEFT);
        $secondHex = str_pad(dechex($second), 2, 0, STR_PAD_LEFT);

        $extra = '';

        if ($value < 25) {
            $extra = '; color: #FFF";';
        }

        return $firstHex . $secondHex . $thirdColorHex . $extra;
    }

    /**
     * @param     $key
     * @param     $sku
     * @param int $age
     *
     * @return float|int
     */
    public function getTotalForColumn($key, $sku, $age = 0)
    {

        if (in_array($key, ['sale_price', 'sale_price_discount', 'unit_price', 'row_total_discount_percentage'])) {
            $total = 0;
            $count = 0;

            foreach ($this->getStatsArray()[$sku] as $day) {
                if (isset($day[$age][$key])) {
                    $total += (int) $day[$age][$key];
                    $count++;
                }
            }

            if ($count == 0) {
                return $total;
            }

            return round($total / $count, 2);
        }
        if (in_array(
            $key,
            [
                'qty',
                'qty_global',
                'qty_discount',
                'row_total',
                'row_total_discount',
                'row_total_global',
                'profit',
                'cost',
                'taxes',
            ]
        )) {
            $total = 0;

            foreach ($this->getStatsArray()[$sku] as $day) {
                if (isset($day[$age][$key])) {
                    $total += (int) $day[$age][$key];
                }
            }

            return number_format($total, 2);
        }

        return round($key, 2);
    }

    /**
     * @param $sku
     *
     * @return bool|\Magento\Catalog\Model\AbstractModel
     */
    public function getProduct($sku)
    {

        return $this->productFactory->create()->loadByAttribute('sku', $sku);
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

    /**
     * @return mixed|null
     */
    public function getSegmentId()
    {

        $segmentId = $this->getRequest()->getParam('segment_id');

        if ($segmentId) {
            return $this->segmentsFactory->create()
                                         ->load($segmentId)
                                         ->getId();
        }

        return null;
    }

    /**
     * @param $age
     *
     * @return string
     */
    public function getAgeName($age)
    {

        if ($this->getType() != 'attribute') {
            return $age;
        }

        return $this->statsFactory->create()->getAgeName($age);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getIntervalTitle()
    {

        switch ($this->getGroup()) {
            case 'weekday':
                $title = 'Week Day';
                break;
            case 'day':
                $title = 'Day';
                break;
            case 'year_month':
                $title = 'Year/Month';
                break;
            case 'year':
                $title = 'Year';
                break;
            case 'month':
                $title = 'Month';
                break;
            default:
                $title = 'Date';
                break;
        }

        return __($title);
    }

    /**
     * @return mixed|string
     */
    public function getAttribute()
    {

        $attribute = $this->getRequest()->getParam('attribute');

        if (!$attribute) {
            $attribute = explode(
                ',',
                $this->_scopeConfig->getValue(
                    'panda_equity/reports/attributes',
                    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
                )
            );
            $attribute = reset($attribute);
        }

        $this->getRequest()->setParam('attribute', $attribute);

        return $attribute;
    }

    /**
     * @return mixed|string
     */
    public function getAttributeValue()
    {

        $attribute = $this->getRequest()->getParam('attributeValue');

        $possible = $this->getPossibleAttributesValues();

        if ((!$attribute || !array_key_exists($attribute, $possible)) && $this->getAttribute2()) {
            $value = key($possible);

            $this->getRequest()->setParam('attributeValue', $value);

            return $value;
        }

        return $attribute;
    }

    /**
     * @return mixed|string
     */
    public function getAttribute2()
    {

        $attribute = $this->getRequest()->getParam('attribute2');

        if ($attribute) {
            $attributes = explode(
                ',',
                $this->_scopeConfig->getValue(
                    'panda_equity/reports/attributes',
                    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
                )
            );
            if (in_array($attribute, $attributes)) {
                return $attribute;
            }
        }

        return false;
    }

    /**
     * @param null $attribute
     *
     * @return array
     */
    public function getPossibleAttributesValues($attribute = null)
    {

        if (!$attribute) {
            $attribute = $this->getAttribute();
        }

        if ($this->possibleAttributesValues) {
            return $this->possibleAttributesValues;
        }
        $info = $this->relationsFactory->create()->getDistinctAttributesValues($attribute);

        $this->possibleAttributesValues = $info;

        return $this->possibleAttributesValues;
    }

    /**
     * @return array
     */
    public function getPossibleAttributes()
    {

        if ($this->possibleAttributes) {
            return $this->possibleAttributes;
        }
        $info = $this->relationsFactory->create()->getPossibleAttributes();

        $this->possibleAttributes = $info;

        return $this->possibleAttributes;
    }

    /**
     * @return array
     */
    public function exportData()
    {

        $collection = $this->getStatsArray();
        $titles = $this->getColumnsTitles(['sku', 'age', 'date', 'previous', 'item_id', 'taxes', 'cost']);
        $skus = $this->getSKUs();
        $days = $this->getPeriodsInCollection();
        $ages = $this->getAges();

        if (count($ages) == 1) {
            $rowSpan = count($ages) + count($skus) - 1;
        } else {
            $rowSpan = count($ages) * count($skus);
        }

        $dataTableTitles = [];
        $dataTableSubTitles = [];
        $dataTableRow = [];
        $dataTableFooter = [];

        $dataTableTitles[] = '';
        $dataTableFooter[] = '';
        $dataTableSubTitles[] = '';

        foreach ($titles as $title) :
            foreach ($skus as $sku) :
                for ($i = 1; $i <= $rowSpan; $i++) :
                    $dataTableTitles[] = (string) __($title);
                endfor;
            endforeach;
        endforeach;

        if (count($ages) > 1) :
            foreach ($titles as $key => $title) :
                foreach ($skus as $sku) :
                    foreach ($ages as $age) :
                        $dataTableSubTitles[] = ($age === 0) ? (string) __('Global') : $age;
                    endforeach;
                endforeach;
            endforeach;
        endif;

        if (count($ages) > 1) :
            foreach ($days as $day) :
                $dataTableRow[$day][] = $day;
                foreach ($titles as $key => $title) :
                    foreach ($skus as $sku) :
                        foreach ($ages as $age) :
                            $dataTableRow[$day][] = $this->getRowValue($key, $collection[$sku][$day][$age][$key]);
                        endforeach;
                    endforeach;
                endforeach;
            endforeach;
        else :
            foreach ($days as $day) :
                foreach ($ages as $age) :
                    $dataTableRow[$day][] = $day;
                    foreach ($titles as $key => $title) :
                        foreach ($skus as $sku) :
                            $dataTableRow[$day][] = $this->getRowValue($key, $collection[$sku][$day][$age][$key]);
                        endforeach;
                    endforeach;
                endforeach;
            endforeach;
        endif;

        foreach ($titles as $key => $title) :
            foreach ($skus as $sku) :
                foreach ($ages as $age) :
                    $dataTableFooter[] = $this->getTotalForColumn($key, $sku, $age);
                endforeach;
            endforeach;
        endforeach;

        return [
            'titles'    => $dataTableTitles,
            'subtitles' => $dataTableSubTitles,
            'rows'      => $dataTableRow,
            'data'      => $dataTableFooter,
        ];
    }
}
