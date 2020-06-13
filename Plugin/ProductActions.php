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

namespace Licentia\Reports\Plugin;

/**
 * Class ProductActions
 *
 * @package Licentia\Panda\Observer
 */
class ProductActions
{

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ProductActions constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scope
     * @param \Magento\Framework\UrlInterface                    $url
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scope,
        \Magento\Framework\UrlInterface $url
    ) {

        $this->scopeConfig = $scope;
        $this->url = $url;
    }

    /**
     * @param \Magento\Catalog\Ui\Component\Listing\Columns\ProductActions $subject
     * @param                                                              $dataSource
     *
     * @return mixed
     */
    public function afterPrepareDataSource(
        \Magento\Catalog\Ui\Component\Listing\Columns\ProductActions $subject,
        $dataSource
    ) {

        if (!$this->scopeConfig->isSetFlag('panda_equity/reports/product_list')) {
            return $dataSource;
        }
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$subject->getData('name')]['performance'] = [
                    'href'   => $this->url->getUrl('pandar/stats/index/sku1', ['sku' => $item['sku']]),
                    'label'  => __('Performance'),
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }
}
