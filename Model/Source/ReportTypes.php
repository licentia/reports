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

namespace Licentia\Reports\Model\Source;

/**
 * Class ReportTypes
 *
 * @package Licentia\Reports\Model\Source
 */
class ReportTypes
{

    const PANDA_REPORT_TYPES = [
        'global'    => 'Global',
        'age'       => 'Age',
        'country'   => 'Country',
        'region'    => 'Region',
        'gender'    => 'Gender',
        'attribute' => 'Attributes',
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {

        return [
            ['value' => 'global', 'label' => 'Global'],
            ['value' => 'age', 'label' => 'Age'],
            ['value' => 'country', 'label' => 'Country'],
            ['value' => 'region', 'label' => 'Region'],
            ['value' => 'gender', 'label' => 'Gender'],
            ['value' => 'attribute', 'label' => 'Attributes'],
        ];
    }
}
