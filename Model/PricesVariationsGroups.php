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
 * @modified   03/06/20, 16:24 GMT
 *
 */

namespace Licentia\Reports\Model;

/**
 * Class PricesVariationsGroups
 *
 * @package Licentia\Reports\Model
 */
class PricesVariationsGroups extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var Sales\PricesVariationFactory
     */
    protected $pricesVariationsFactory;

    /**
     * PricesVariationsGroups constructor.
     *
     * @param Sales\PricesVariationFactory                                 $pricesVariationFactory
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Licentia\Reports\Model\Sales\PricesVariationFactory $pricesVariationFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->pricesVariationsFactory = $pricesVariationFactory;
    }

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'panda_prices_variation_groups';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'panda_prices_variation_groups';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_init(ResourceModel\PricesVariationsGroups::class);
    }

    /**
     * @param $itemId
     *
     * @return $this
     */
    public function setItemId($itemId)
    {

        return $this->setData('item_id', $itemId);
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function setName($name)
    {

        return $this->setData('name', $name);
    }

    /**
     * @param $groups
     *
     * @return $this
     */
    public function setGroups($groups)
    {

        return $this->setData('groups', $groups);
    }

    /**
     * @return mixed
     */
    public function getItemId()
    {

        return $this->getData('item_id');
    }

    /**
     * @return mixed
     */
    public function getName()
    {

        return $this->getData('name');
    }

    /**
     * @return mixed
     */
    public function getGroups()
    {

        return $this->getData('groups');
    }
}
