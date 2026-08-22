<?php

namespace HyvaElsner\ProductPage\Block\Popup;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Helper\PostHelper;

class Suggest extends AbstractProduct
{

    /**
     * Catalog product model.
     *
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * Catalog product visibility helper.
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $catalogProductVisibility;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Post data helper.
     *
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    private $postDataHelper;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     */
    public function __construct(
        Context $context,
        Visibility $catalogProductVisibility,
        Product $product,
        PostHelper $postDataHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->product = $product;
        $this->scopeConfig = $context->getScopeConfig();
        $this->postDataHelper = $postDataHelper;
    }

    /**
     * Get post data helper.
     *
     * @return \Magento\Framework\Data\Helper\PostHelper
     */
    public function getPostDataHelper()
    {
        return $this->postDataHelper;
    }

    /**
     * Get suggest title.
     *
     * @return string
     */
    public function getSuggestTitle()
    {
        return $this->scopeConfig->getValue(
            'elsner_recommend_product/recommend_product_popup/suggest_title',
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get suggested source.
     *
     * @return int
     */
    public function getSuggestSource()
    {
        return $this->scopeConfig->getValue(
            'elsner_recommend_product/recommend_product_popup/suggest_source',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get suggested limit.
     *
     * @return int
     */
    public function getSuggestLimit()
    {
        return $this->scopeConfig->getValue(
            'elsner_recommend_product/recommend_product_popup/suggest_limit',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show suggested products.
     *
     * @return bool
     */
    public function isShowSuggestBlock()
    {
        return $this->scopeConfig->isSetFlag(
            'elsner_recommend_product/recommend_product_popup/suggest_product',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get suggested product collection.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
     */
    public function getProductCollection()
    {
        $product = $this->getProduct();
        $source = $this->getSuggestSource();

        switch ($source) {
            case \HyvaElsner\ProductPage\Model\Config\Source\Suggest::SUGGEST_SOURCE_RELATED:
                $collection = $product->getRelatedProductCollection();
                break;
            case \HyvaElsner\ProductPage\Model\Config\Source\Suggest::SUGGEST_SOURCE_UPSELL:
                $collection = $product->getUpSellProductCollection();
                break;
            case \HyvaElsner\ProductPage\Model\Config\Source\Suggest::SUGGEST_SOURCE_XSELL:
                $collection = $product->getCrossSellProductCollection();
                break;
            default:
                $collection = $product->getRelatedProductCollection();
        }

        $collection->addAttributeToSelect('required_options')->setPositionOrder()->addStoreFilter();
        $this->_addProductAttributesAndPrices($collection);
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        $limit = $this->getSuggestLimit();

        if ($limit && $limit > 0) {
            $collection->setPageSize($limit);
        }

        $collection->load();

        return $collection;
    }

    /**
     * Get suggested block title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getSuggestTitle();
    }

    /**
     * Get added product.
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->product->getId()) {
            $productId = $this->getProductId();
            $this->product->load($productId);
        }

        return $this->product;
    }
}
