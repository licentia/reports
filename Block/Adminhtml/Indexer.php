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

namespace Licentia\Reports\Block\Adminhtml;

/**
 * Class Indexer
 *
 * @package Licentia\Reports\Block\Adminhtml
 */
class Indexer extends \Magento\Backend\Block\Widget\Grid\Container
{

    protected function _construct()
    {

        $this->_blockGroup = 'Licentia_Reports';
        $this->_controller = 'adminhtml_indexer';
        $this->_headerText = __('Indexers');

        parent::_construct();

        $this->buttonList->remove('add');

        $location = $this->getUrl('*/*/reindex', ['op' => 'all']);
        $this->buttonList->add(
            'refresh',
            [
                "label"   => __("Rebuild All Values"),
                "onclick" => "if(!confirm('" . __('Are you sure?') . "')){return false;}; window.location='$location'",
            ]
        );
    }
}
