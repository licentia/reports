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

    /**
     * @param $version
     *
     * @return string
     */
    public static function getAgeMySQLGroup($version)
    {

        if (version_compare('5.7.0', $version, '<')) {

            return "IF(age IS NULL,predicted_age,CASE  
                              WHEN age >= 18 AND age <= 24 THEN '18-24'  
                              WHEN age >=25 AND age <=34 THEN '25-34'
                              WHEN age >=35 AND age <=45 THEN '35-44'
                              WHEN age >=45 AND age <= 54 THEN '45-54'  
                              WHEN age >=55 AND age <=64 THEN '55-64'  
                              WHEN age >=65 THEN '65+'   
                            END)";

        } else {

            return "IF(age IS NULL,ANY_VALUE(predicted_age),CASE  
                              WHEN age >= 18 AND age <= 24 THEN '18-24'  
                              WHEN age >=25 AND age <=34 THEN '25-34'
                              WHEN age >=35 AND age <=45 THEN '35-44'
                              WHEN age >=45 AND age <= 54 THEN '45-54'  
                              WHEN age >=55 AND age <=64 THEN '55-64'  
                              WHEN age >=65 THEN '65+'   
                            END)";
        }
    }

}
