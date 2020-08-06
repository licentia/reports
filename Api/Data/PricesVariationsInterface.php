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

namespace Licentia\Reports\Api\Data;

/**
 * Interface PricesVariationsInterface
 *
 * @package Licentia\Reports\Api\Data
 */
interface PricesVariationsInterface
{

    /**
     *
     */
    const SKU = 'sku';

    /**
     *
     */
    const NAME = 'name';

    /**
     *
     */
    const PRICE = 'price';

    /**
     *
     */
    const QTY = 'qty';

    /**
     *
     */
    const DEVIATION = 'deviation';

    /**
     *
     */
    const FIRST_SALE_AT = 'first_sale_at';

    /**
     *
     */
    const LAST_SALE_AT = 'last_sale_at';

    /**
     * Get sku
     *
     * @return string|null
     */

    public function getSku();

    /**
     * Set sku
     *
     * @param string $sku
     *
     * @return PricesVariationsSearchResultsInterface
     */

    public function setSku($sku);

    /**
     * Get $name
     *
     * @return string|null
     */

    public function getName();

    /**
     * Set name
     *
     * @param string $name
     *
     * @return PricesVariationsSearchResultsInterface
     */

    public function setName($name);

    /**
     * Get price
     *
     * @return string|null
     */

    public function getPrice();

    /**
     * Set $price
     *
     * @param string $price
     *
     * @return PricesVariationsSearchResultsInterface
     */

    public function setPrice($price);

    /**
     * Get qty
     *
     * @return string|null
     */

    public function getQty();

    /**
     * Set qty
     *
     * @param string $qty
     *
     * @return PricesVariationsSearchResultsInterface
     */

    public function setQty($qty);

    /**
     * Get deviation
     *
     * @return string|null
     */

    public function getDeviation();

    /**
     * Set deviation
     *
     * @param string $deviation
     *
     * @return PricesVariationsSearchResultsInterface
     */

    public function setDeviation($deviation);

    /**
     * Get last sale date
     *
     * @return string|null
     */

    public function getLastSaleAt();

    /**
     * Set last sale date
     *
     * @param string $lastSale
     *
     * @return PricesVariationsSearchResultsInterface
     */

    public function setLastSaleAt($lastSale);

    /**
     * Get first sale date
     *
     * @return string|null
     */

    public function getFirstSaleAt();

    /**
     * Set last sale date
     *
     * @param string $firstSale
     *
     * @return PricesVariationsSearchResultsInterface
     */

    public function setFirstSaleAt($firstSale);

}
