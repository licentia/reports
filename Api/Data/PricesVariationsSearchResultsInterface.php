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

namespace Licentia\Reports\Api\Data;

/**
 * Interface RecordsSearchResultsInterface
 *
 * @package Licentia\Panda\Api\Data
 */
interface PricesVariationsSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Records list.
     *
     * @return \Licentia\Equity\Api\Data\RecordsInterface[]
     */

    public function getItems();

    /**
     * Set record_id list.
     *
     * @param \Licentia\Equity\Api\Data\RecordsInterface[] $items
     *
     * @return $this
     */

    public function setItems(array $items);
}
