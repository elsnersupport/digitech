<?php

namespace Mageplaza\CustomShopbybrand\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Shopbybrand\Block\Brand\BrandList;

/**
 * Class ShowMore
 * @package Mageplaza\CustomShopbybrand\Controller\Index
 */
class ShowMore extends Action
{
    /**
     * @type PageFactory
     */
    protected $resultPageFactory;

    /**
     * @type BrandList
     */
    protected $brandList;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param BrandList $brandList
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        BrandList $brandList
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->brandList         = $brandList;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page|void
     * @throws LocalizedException
     */
    public function execute()
    {
        $pager = $this->brandList->getLayout()->createBlock(\Mageplaza\Shopbybrand\Block\Brand\BrandList::class)
            ->setTemplate('Mageplaza_CustomShopbybrand::brand/list/showMore.phtml')->toHtml();

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData(['data' => $pager]);
        return $resultJson;
    }
}
