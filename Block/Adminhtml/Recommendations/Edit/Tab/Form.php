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

namespace Licentia\Reports\Block\Adminhtml\Recommendations\Edit\Tab;

/**
 * Class Form
 *
 * @package Licentia\Panda\Block\Adminhtml\Recommendations\Edit\Tab
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected \Licentia\Reports\Helper\Data $pandaHelper;

    /**
     * @var \Licentia\Equity\Model\SegmentsFactory
     */
    protected \Licentia\Equity\Model\SegmentsFactory $segmentsFactory;

    /**
     * Form constructor.
     *
     * @param \Licentia\Equity\Model\SegmentsFactory  $segmentsFactory
     * @param \Licentia\Reports\Helper\Data           $pandaHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param array                                   $data
     */
    public function __construct(
        \Licentia\Equity\Model\SegmentsFactory $segmentsFactory,
        \Licentia\Reports\Helper\Data $pandaHelper,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {

        parent::__construct($context, $registry, $formFactory, $data);

        $this->pandaHelper = $pandaHelper;
        $this->segmentsFactory = $segmentsFactory;
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {

        parent::_construct();
        $this->setId('block_form');
        $this->setTitle(__('Block Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {

        /** @var \Licentia\Reports\Model\Recommendations $model */
        $model = $this->_coreRegistry->registry('panda_recommendation');

        if ($this->getRequest()->getParam('entity_type')) {
            $model->setEntityType($this->getRequest()->getParam('entity_type'));
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'     => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                ],
            ]
        );

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );

        if ($model->getEntityType()) {
            $fieldset->addField('entity_type', 'hidden', ['value' => $model->getEntityType(), 'name' => 'entity_type']);
        }

        $fieldset->addField(
            'internal_name',
            'text',
            [
                'name'     => 'internal_name',
                'label'    => __('Internal Name'),
                'title'    => __('Internal Name'),
                "required" => true,
            ]
        );

        $fieldset->addField(
            'title',
            'text',
            [
                'name'     => 'title',
                'label'    => __('Title'),
                'title'    => __('Title'),
                "required" => true,
            ]
        );

        $fieldset->addField(
            'code',
            'text',
            [
                'name'     => 'code',
                'label'    => __('Code'),
                'title'    => __('Code'),
                "required" => true,
                "class"    => 'small_input validate-code ',
            ]
        );

        $entityType = strtolower($model->getEntityType());

        if ($model->getEntityType() == 'category') {
            $fieldset->addField(
                'category',
                'select',
                [
                    'name'     => 'category',
                    'label'    => __('Category'),
                    'title'    => __('Category'),
                    "required" => true,
                    "class"    => 'small_input',
                    'values'   => $this->pandaHelper->getCategories(),
                ]
            );
        }

        if ($entityType == 'engine') {
            $html = '<script type="text/javascript">

                require(["jquery"],function ($){
    
                    toggleControlsSource = {
                        run: function() {
                            if($("#based_on").val()  == "specific_product"){
                                $("#skus").parent().parent().show();
                                $("#skus").addClass("required");
                                $("#skus").addClass("required-entry");
                            }else{
                                $("#skus").removeClass("required");
                                $("#skus").removeClass("required-entry");
                                $("#skus").parent().parent().hide();
                            }
                        }
                    }
                    window.toggleControlsSource = toggleControlsSource;
                    $(function() {
                        toggleControlsSource.run();
                    });

                });
                </script>
                ';

            $fieldset->addField(
                "based_on",
                "select",
                [
                    "label"    => __("Recommendation Source"),
                    "options"  => [
                        'purchase_history' => __('Purchase History'),
                        'specific_product' => __('Specific product'),
                        'current_product'  => __('Current Product'),
                    ],
                    "required" => true,
                    "name"     => "based_on",
                    'onchange' => 'toggleControlsSource.run()',
                ]
            )
                     ->setAfterElementHtml($html);

            $fieldset->addField(
                'skus',
                'text',
                [
                    'name'     => 'skus',
                    'label'    => __('Skus'),
                    'title'    => __('Skus'),
                    'note'     => __('Separate multiples with a comma , '),
                    "required" => true,
                ]
            );

            $fieldset->addField(
                "level",
                "select",
                [
                    "label"    => __("How wide should we search"),
                    "options"  => [
                        'none'         => __('Directly Related'),
                        'after_order'  => __('After Order Only'),
                        'second_level' => __('2 levels'),
                    ],
                    "required" => true,
                    "name"     => "level",
                ]
            );

            $fieldset->addField(
                "segment_drill",
                "select",
                [
                    "label"    => __("Recommend Based On Customer"),
                    "options"  => [
                        'none'    => __('None/Ignore'),
                        'age'     => __('Age'),
                        'gender'  => __('Gender'),
                        'country' => __('Country'),
                        'region'  => __('Region'),
                    ],
                    "required" => true,
                    "name"     => "segment_drill",
                ]
            );

            $fieldset->addField(
                "use_segments",
                "select",
                [
                    "label"    => __("Use Segments"),
                    "options"  => ['1' => __('Yes'), '0' => __('No')],
                    "required" => true,
                    "name"     => "use_segments",
                ]
            );
        }

        $fieldset2 = $form->addFieldset(
            'base_fieldset_fail',
            ['legend' => __('If the selection above returns no results'), 'class' => 'fieldset-wide']
        );

        $html = '<script type="text/javascript">

                require(["jquery"],function ($){
    
                    toggleControlsIfFail = {
                        run: function() {
                            if($("#if_fail").val()  == "category"){
                                $("#category_fail").parent().parent().show();
                            }else{
                                $("#category_fail").parent().parent().hide();
                            }
                        }
                    }
                    window.toggleControlsIfFail = toggleControlsIfFail;
                    $(function() {
                        toggleControlsIfFail.run();
                    });

                });
                </script>
                ';

        $fieldset2->addField(
            "if_fail",
            "select",
            [
                "label"    => __("Use: "),
                "options"  => [
                    'views'    => __('Most Viewed Products'),
                    'category' => __('Products in a specific Category'),
                    'recent'   => __('Recent Products'),
                    'none'     => __("Leave Empty"),
                ],
                'onchange' => 'toggleControlsIfFail.run()',
                "required" => true,
                "name"     => "if_fail",
            ]
        )
                  ->setAfterElementHtml($html);

        $fieldset2->addField(
            'category_fail',
            'select',
            [
                'name'     => 'category_fail',
                'label'    => __('Category if not Results'),
                'title'    => __('Category if not Results'),
                "required" => true,
                "class"    => 'small_input',
                'values'   => $this->pandaHelper->getCategories(),
            ]
        );

        $fieldset3 = $form->addFieldset(
            'base_fieldset_meta',
            ['legend' => __('Meta Info'), 'class' => 'fieldset-wide']
        );

        $fieldset3->addField(
            "number_products",
            "text",
            [
                "label"    => __("Number of Products"),
                "required" => true,
                "class"    => 'validate-digits small_input',
                "name"     => "number_products",
            ]
        );

        $fieldset3->addField(
            "sort_results",
            "select",
            [
                "label"    => __("Sort Results"),
                "options"  => [
                    'random'     => __('Randomly'),
                    'created_at' => __('Recently Added'),
                    'price_asc'  => __('Price ASC'),
                    'price_desc' => __('Prices DESC'),
                ],
                "required" => true,
                "name"     => "sort_results",
            ]
        );

        $fieldset3->addField(
            "is_active",
            "select",
            [
                "label"    => __("Is Recommendation Active"),
                "options"  => ['1' => __('Yes'), '0' => __('No')],
                "required" => true,
                "name"     => "is_active",
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat();

        $fieldset3->addField(
            'from_date',
            'date',
            [
                'name'        => 'from_date',
                'date_format' => $dateFormat,
                'label'       => __('Active From Date'),
            ]
        );

        $fieldset3->addField(
            'to_date',
            'date',
            [
                'name'        => 'to_date',
                'date_format' => $dateFormat,
                'label'       => __('Active To Date'),
            ]
        );

        $form->addValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
