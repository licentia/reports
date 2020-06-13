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

namespace Licentia\Reports\Plugin;

/**
 * Class SearchHistory
 *
 * @package Licentia\Panda\Observer
 */
class SearchHistory
{

    /**
     * @param \Magento\Search\Model\ResourceModel\Query $subject
     * @param null                                      $result
     * @param \Magento\Search\Model\Query               $query
     *
     * @return null
     */
    public function afterSaveNumResults(
        \Magento\Search\Model\ResourceModel\Query $subject,
        $result = null,
        \Magento\Search\Model\Query $query
    ) {

        try {
            $subject->getConnection()
                    ->insert(
                        $subject->getTable('panda_search_history'),
                        [
                            'query'      => $query->getQueryText(),
                            'results'    => $query->getNumResults(),
                            'created_at' => new \Zend_Db_Expr('NOW()'),
                            'store_id'   => $query->getStoreId(),
                        ]
                    );
        } catch (\Exception $e) {
        }

        return $result;
    }
}
