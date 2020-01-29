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

namespace Licentia\Reports\Block\Adminhtml\PricesVariationsGroups\Edit\Tab;

/**
 * Class Form
 *
 * @package Licentia\Reports\Block\Adminhtml\PricesVariationsGroups\Edit\Tab
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     *
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $customerGroup;

    /**
     * Form constructor.
     *
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup
     * @param \Magento\Backend\Block\Template\Context                $context
     * @param \Magento\Framework\Registry                            $registry
     * @param \Magento\Framework\Data\FormFactory                    $formFactory
     * @param array                                                  $data
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {

        $this->customerGroup = $customerGroup;
        parent::__construct($context, $registry, $formFactory, $data);
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

        $model = $this->_coreRegistry->registry('panda_variation_group');

        $groups = $this->customerGroup->toOptionArray();

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

        $form->setHtmlIdPrefix('subscriber_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'name',
            'text',
            ['name' => 'name', 'label' => __('Name'), 'title' => __('Name'), 'required' => true]
        );

        $fieldset->addField(
            "groups",
            "multiselect",
            [
                "label"    => __("Groups"),
                "class"    => "required-entry",
                "values"   => $groups,
                "required" => true,
                "name"     => "groups[]",

            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
