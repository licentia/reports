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

namespace Licentia\Reports\Model\Source;

/**
 * Class PricesVariations
 *
 * @package Licentia\Reports\Model\Source
 */
class PricesVariationsGroups implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Licentia\Reports\Model\Sales\PricesVariationFactory
     */
    protected $pricesVariationsGroupsFactory;

    /**
     * PricesVariations constructor.
     *
     * @param \Licentia\Reports\Model\PricesVariationsGroupsFactory $pricesVariationsGroupsFactory
     */
    public function __construct(
        \Licentia\Reports\Model\PricesVariationsGroupsFactory $pricesVariationsGroupsFactory
    ) {

        $this->pricesVariationsGroupsFactory = $pricesVariationsGroupsFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {

        $collection = $this->pricesVariationsGroupsFactory->create()->getCollection();

        $return = [];

        foreach ($collection as $item) {
            $return[] = ['value' => $item->getId(), 'label' => $item->getName()];
        }

        return $return;
    }
}
