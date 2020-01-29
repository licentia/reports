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
