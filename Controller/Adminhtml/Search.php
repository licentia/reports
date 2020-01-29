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

namespace Licentia\Reports\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Class Stats
 *
 * @package Licentia\Panda\Controller\Adminhtml
 */
class Search extends \Magento\Backend\App\Action
{

    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Licentia_Reports::search';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * @var \Licentia\Reports\Model\SearchFactory
     */
    protected $searchFactory;

    /**
     * Items constructor.
     *
     * @param Action\Context                                    $context
     * @param \Licentia\Reports\Model\SearchFactory             $searchFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date    $dateFilter
     * @param \Magento\Framework\View\Result\PageFactory        $resultPageFactory
     * @param \Magento\Framework\Registry                       $registry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory  $fileFactory
     * @param \Magento\Framework\View\Result\LayoutFactory      $resultLayoutFactory
     */
    public function __construct(
        Action\Context $context,
        \Licentia\Reports\Model\SearchFactory $searchFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {

        $this->searchFactory = $searchFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->fileFactory = $fileFactory;
        $this->layoutFactory = $resultLayoutFactory;
        $this->dateFilter = $dateFilter;

        parent::__construct($context);
    }

    /**
     *
     */
    public function execute()
    {
    }

}
