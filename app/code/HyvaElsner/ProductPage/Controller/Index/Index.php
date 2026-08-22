<?php

namespace HyvaElsner\ProductPage\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use HyvaElsner\ProductPage\Block\Popup\Suggest;
// use Magento\Framework\Controller\Result\JsonFactory;

class Index extends Action
{
    protected $productRepository;
    protected $productCollectionFactory;
    protected $layoutFactory;

    /**
     * Result page factory.
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    // /**
    //  * @var JsonFactory
    //  */
    // protected $resultJsonFactory;

    /**
     * Suggest Block
     *
     * @var Suggest
     */
    public $suggest;

    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        CollectionFactory $productCollectionFactory,
        LayoutFactory $layoutFactory,
        PageFactory $resultPageFactory,
        // JsonFactory $resultJsonFactory,
        Suggest $suggest
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->layoutFactory = $layoutFactory;
        $this->resultPageFactory = $resultPageFactory;
        // $this->resultJsonFactory = $resultJsonFactory;
        $this->suggest = $suggest;
    }

    public function execute()
    {
        // $resultJson = $this->resultJsonFactory->create();   
        // $productId = $this->getRequest()->getParam('product_id');
        $data = json_decode($this->getRequest()->getContent(), true);
        if (!empty($data)) {
            $productId = $data['productId'];
        } else {
            $productId = false;
        }

        if (!$productId) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'success' => false,
                'message' => __('Product ID is required.')
            ]);
        }

        try {
            $resultPage = $this->resultPageFactory->create();

            $product = $this->productRepository->getById($productId);
            $html = "";
            if ($this->suggest->isShowSuggestBlock()) {
                $suggestBlock = $resultPage->getLayout()
                    ->createBlock(\HyvaElsner\ProductPage\Block\Popup\Suggest::class)
                    ->setTemplate('HyvaElsner_ProductPage::popup/suggest.phtml')
                    ->setProductId($product->getId());
                $html = $suggestBlock->toHtml();
            }



            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
