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
     * @return \Licentia\Reports\Api\Data\PricesVariationsSearchResultsInterface
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
     * @return \Licentia\Reports\Api\Data\PricesVariationsSearchResultsInterface
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
     * @return \Licentia\Reports\Api\Data\PricesVariationsSearchResultsInterface
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
     * @return \Licentia\Reports\Api\Data\PricesVariationsSearchResultsInterface
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
     * @return \Licentia\Reports\Api\Data\PricesVariationsSearchResultsInterface
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
     * @return \Licentia\Reports\Api\Data\PricesVariationsSearchResultsInterface
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
     * @return \Licentia\Reports\Api\Data\PricesVariationsSearchResultsInterface
     */

    public function setFirstSaleAt($firstSale);

}
