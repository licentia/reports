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

namespace Licentia\Reports\Block\Adminhtml;

/**
 * Class Templates
 *
 * @package Licentia\Panda\Block\Adminhtml
 */
class Recommendations extends \Magento\Backend\Block\Widget\Container
{

    protected function _construct()
    {

        parent::_construct();

        $this->_blockGroup = 'Licentia_Reports';
        $this->_controller = 'adminhtml_recommendations';
        $this->_headerText = __('Recommendations');
    }

    /**
     * @return \Magento\Backend\Block\Widget\Container
     */
    protected function _prepareLayout()
    {

        $addButtonProps = [
            'id'           => 'add_new_recommendations',
            'label'        => __('Product Recommendation Source'),
            'class'        => 'add',
            'button_class' => '',
            'class_name'   => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options'      => $this->_getAddProductButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * Get dropdown options for save split button
     *
     * @return array
     */
    protected function _getAddProductButtonOptions()
    {

        $options = [];

        $types = \Licentia\Reports\Model\Recommendations::getRecommendationsTypes();

        foreach ($types as $key => $store) {
            $options[] = [
                'label'   => __($store),
                'onclick' => "window.location='" . $this->getUrl(
                        '*/*/new',
                        [
                            'entity_type' => $key,
                        ]
                    ) . "'",
                'default' => true,
            ];
        }

        return $options;
    }
}
