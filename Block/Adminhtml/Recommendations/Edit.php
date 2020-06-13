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

namespace Licentia\Reports\Block\Adminhtml\Recommendations;

/**
 * Class Edit
 *
 * @package Licentia\Panda\Block\Adminhtml\Recommendations
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry = null;

    /**
     * @var \Licentia\Reports\Model\RecommendationsFactory
     */
    protected $recommendationFactory;

    /**
     * @param \Licentia\Reports\Model\RecommendationsFactory $recommendationFactory
     * @param \Magento\Backend\Block\Widget\Context          $context
     * @param \Magento\Framework\Registry                    $registry
     * @param array                                          $data
     */
    public function __construct(
        \Licentia\Reports\Model\RecommendationsFactory $recommendationFactory,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {

        $this->recommendationFactory = $recommendationFactory;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {

        $this->_blockGroup = 'Licentia_Reports';
        $this->_controller = 'adminhtml_recommendations';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Recommendation'));
        $this->buttonList->update('delete', 'label', __('Delete Recommendation'));

        $this->buttonList->remove('save');
        $this->getToolbar()
             ->addChild(
                 'save-split-button',
                 'Magento\Backend\Block\Widget\Button\SplitButton',
                 [
                     'id'           => 'save-split-button',
                     'label'        => __('Save'),
                     'class_name'   => 'Magento\Backend\Block\Widget\Button\SplitButton',
                     'button_class' => 'widget-button-update',
                     'options'      => [
                         [
                             'id'             => 'save-button',
                             'label'          => __('Save'),
                             'default'        => true,
                             'data_attribute' => [
                                 'mage-init' => [
                                     'button' => [
                                         'event'  => 'saveAndContinueEdit',
                                         'target' => '#edit_form',
                                     ],
                                 ],
                             ],
                         ],
                         [
                             'id'             => 'save-continue-button',
                             'label'          => __('Save & Close'),
                             'data_attribute' => [
                                 'mage-init' => [
                                     'button' => [
                                         'event'  => 'save',
                                         'target' => '#edit_form',
                                     ],
                                 ],
                             ],
                         ],
                     ],
                 ]
             );
    }

    /**
     * Get edit form container header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {

        $recommendation = $this->registry->registry('current_recommendation');

        if ($recommendation) {
            return __(
                "Edit Recommendation '%1'",
                $this->escapeHtml(
                    $this->registry->registry('panda_recommendation')
                                   ->getInternalName()
                )
            );
        } else {
            return __("New Recommendation");
        }
    }
}
