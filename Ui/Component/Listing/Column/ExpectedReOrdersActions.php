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

namespace Licentia\Reports\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class BlockActions
 */
class ExpectedReOrdersActions extends Column
{

    /**
     * Url path
     */
    const URL_PATH_VIEW = 'pandar/expectedreorders/view';

    const URL_PATH_HOLD = 'pandar/expectedreorders/hold';

    const URL_PATH_UNHOLD = 'pandar/expectedreorders/unhold';

    const URL_PATH_CUSTOMER = 'customer/index/edit';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * Constructor
     *
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param ContextInterface                          $context
     * @param UiComponentFactory                        $uiComponentFactory
     * @param UrlInterface                              $urlBuilder
     * @param array                                     $components
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Framework\AuthorizationInterface $authorization,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {

        $this->authorization = $authorization;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $items
     *
     * @return array
     */
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['item_id'])) {
                    /* $item[$this->getData('name')]['view'] = [
                         'href'  => $this->urlBuilder->getUrl(
                             static::URL_PATH_VIEW,
                             [
                                 'id' => $item['item_id'],
                             ]
                         ),
                         'label' => __('View'),
                     ];*/

                    if ($item['locked'] == 1) {
                        $item[$this->getData('name')]['unhold'] = [
                            'href'  => $this->urlBuilder->getUrl(
                                static::URL_PATH_UNHOLD,
                                [
                                    'id' => $item['item_id'],
                                ]
                            ),
                            'label' => __('Unhold'),
                        ];
                    }

                    if (is_int($item['customer_id']) && $item['customer_id'] > 0) {
                        $item[$this->getData('name')]['unhold'] = [
                            'href'  => $this->urlBuilder->getUrl(
                                static::URL_PATH_CUSTOMER,
                                [
                                    'id' => $item['customer_id'],
                                ]
                            ),
                            'label' => __('Customer'),
                        ];
                    }

                    if ($item['locked'] == 0) {
                        $item[$this->getData('name')]['hold'] = [
                            'href'  => $this->urlBuilder->getUrl(
                                static::URL_PATH_HOLD,
                                [
                                    'id' => $item['item_id'],
                                ]
                            ),
                            'label' => __('Hold'),
                        ];
                    }
                }
            }
        }

        return $dataSource;
    }
}
