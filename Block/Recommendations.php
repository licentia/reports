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

namespace Licentia\Reports\Block;

/**
 * Class Recommendations
 *
 * @package Licentia\Reports\Block
 */
class Recommendations extends \Magento\Catalog\Block\Product\AbstractProduct
    implements \Magento\Widget\Block\BlockInterface
{

    /**
     * @var \Licentia\Reports\Helper\Data
     */
    protected \Licentia\Reports\Helper\Data $pandaHelper;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected \Magento\Catalog\Helper\Output $helperOutput;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected \Magento\Framework\Url\EncoderInterface $urlEncoder;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected \Magento\Framework\Data\Form\FormKey $formKey;

    /**
     * @var \Licentia\Reports\Model\RecommendationsFactory
     */
    protected \Licentia\Reports\Model\RecommendationsFactory $recommendationsFactory;

    /**
     * Recommendations constructor.
     *
     * @param \Magento\Framework\Data\Form\FormKey           $formKey
     * @param \Magento\Framework\Url\EncoderInterface        $encoder
     * @param \Magento\Catalog\Block\Product\Context         $context
     * @param \Magento\Catalog\Helper\Output                 $helperOutput
     * @param \Licentia\Reports\Model\RecommendationsFactory $recommendationsFactory
     * @param \Licentia\Reports\Helper\Data                  $pandaHelper
     * @param array                                          $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Url\EncoderInterface $encoder,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Helper\Output $helperOutput,
        \Licentia\Reports\Model\RecommendationsFactory $recommendationsFactory,
        \Licentia\Reports\Helper\Data $pandaHelper,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->recommendationsFactory = $recommendationsFactory;
        $this->pandaHelper = $pandaHelper;
        $this->helperOutput = $helperOutput;
        $this->urlEncoder = $encoder;
        $this->formKey = $formKey;
    }

    /**
     * @return string
     */
    public function getFormKey()
    {

        return $this->formKey->getFormKey();
    }

    /**
     * @param $url
     *
     * @return string
     */
    public function encodeUrl($url)
    {

        return $this->urlEncoder->encode($url);
    }

    /**
     * @return \Licentia\Reports\Helper\Data
     */
    public function getPandaHelper()
    {

        return $this->pandaHelper;
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {

        return $this->_coreRegistry;
    }

    /**
     * @return bool
     */
    public function showCart()
    {

        return $this->_coreRegistry->registry('panda_campaign_environment') ? false : true;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {

        $cacheEnabled = $this->pandaHelper->isCacheEnabled();

        $params = $this->getData();

        if ($cacheEnabled &&
            !$this->getRequest()->isPost() &&
            #!$this->pandaHelper->getCustomerEmail() &&
            !$this->_coreRegistry->registry('panda_campaign_environment')) {
            $params['uri'] = $this->getRequest()->getServer('REQUEST_URI');

            unset($params['widget_place_holder_height'], $params['module_name'], $params['type'], $params['widget_place_holder_width']);

            $params['params'] = json_encode(
                [
                    'c' => $this->getRequest()
                                ->getControllerName(),
                    'a' => $this->getRequest()
                                ->getActionName(),
                    'm' => $this->getRequest()
                                ->getModuleName(),
                    'i' => $this->getRequest()->getParam('id', 0),
                ]
            );

            $this->setTemplate('empty.phtml');

            $css = '';
            if ($this->getData('widget_place_holder_width')) {
                $css .= " width: " . $this->getData('widget_place_holder_width') . ';';
            }
            if ($this->getData('widget_place_holder_height')) {
                $css .= " height: " . $this->getData('widget_place_holder_height') . ';';
            }

            $url = $this->_urlBuilder->getUrl('pandar/recommendations/get');

            $jsContent = "<script type='text/javascript'>
        
                        require(['jquery', 'domReady!'], function ($) {
                    
                            $.ajax({
                                url: '{$url}',
                                type: 'POST',
                                context: document.body,
                                success: function (responseText) {
                                    $('#panda_recommendations').html(responseText);
                                },
                                data:  " . json_encode($params) . "
                            });
                    
                        });
                    
                    </script>
                    <div style='{$css}' id='panda_recommendations'></div> ";

            $this->setContent($jsContent);
        } else {
            if ($cacheEnabled) {
                $params = $this->getRequest()->getParams();
                $this->addData($params);
            }

            try {
                $model = $this->recommendationsFactory->create();
                $model->loadFromCode($params['widget_code'])
                      ->addData($params);

                $this->pandaHelper->registerCurrentScope();

                $this->setTemplate($params['widget_block_template']);

                if (!$model->getData('title') || !isset($params['widget_title'])) {
                    $params['widget_title'] = '';
                }

                $collection = $model->getRecommendationsCollection();
                $this->setData('product_collection', $collection);
                $this->setData('loaded_product_collection', $collection);
                $this->setData('title', $params['widget_title']);
            } catch (\Exception $e) {
            }
        }

        return parent::_toHtml();
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param string                         $priceType
     * @param string                         $renderZone
     * @param array                          $arguments
     *
     * @return string
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {

        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['price_id'] = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                $arguments
            );
        }

        return $price;
    }
}
