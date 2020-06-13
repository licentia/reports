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

namespace Licentia\Reports\Helper;

/**
 * Class Data
 *
 * @package Licentia\Reports\Helper
 */
class Data extends \Licentia\Panda\Helper\Data
{

    /**
     * @param $indexer
     *
     * @return mixed
     */
    public function getRebuildDateforIndexer($indexer)
    {

        $updateDate = $this->indexerCollection->create()
                                              ->addFieldToFilter('indexer_id', $indexer)
                                              ->getFirstItem()
                                              ->getData('updated_at');

        if (!$updateDate) {
            return __('Never');
        }

        return $this->timezone->formatDateTime($updateDate);

    }

}
