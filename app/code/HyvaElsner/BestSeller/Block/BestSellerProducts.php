<?php

namespace HyvaElsner\BestSeller\Block;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestSellersCollectionFactory;
use Magento\Catalog\Model\Category;
use Magento\Framework\Registry;

/**
 * Class BestSellerProducts
 * @package HyvaElsner\BestSeller\Block
 */
class BestSellerProducts extends AbstractProduct
{
    /**
     * @var BestSellersCollectionFactory
     */
    protected $_bestSellersCollectionFactory;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var int
     */
    protected $_productsCount = 12;

    /**
     * @var int
     */
    protected $_popularProductsCount = 6;

    /**
     * Registry instance for storing shared data.
     *
     * @var Registry
     */
    protected $_registry;

    protected $_logger;

    /**
     * BestSellerProducts constructor.
     *
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param DateTime $dateTime
     * @param HttpContext $httpContext
     * @param BestSellersCollectionFactory $bestSellersCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        DateTime $dateTime,
        HttpContext $httpContext,
        BestSellersCollectionFactory $bestSellersCollectionFactory,
        Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_bestSellersCollectionFactory = $bestSellersCollectionFactory;
        $this->_registry = $registry;
        $this->_logger = $logger;
        parent::__construct($context, $data);
    }

    /**
     * Get Current Category Function
     *
     * @return string
     */
    public function getCurrentCategory()
    {
        $category = $this->_registry->registry('current_category');
        if ($category instanceof \Magento\Catalog\Model\Category) {
            return $category;
        }
        return null;
    }

    /**
     * Set products count
     *
     * @param int $count
     * @return $this
     */
    public function setProductsCount($count)
    {
        $this->_productsCount = $count;
        return $this;
    }

    /**
     * Get products count
     *
     * @return int
     */
    public function getProductsCount()
    {
        return $this->_productsCount;
    }

    /**
     * Set products count
     *
     * @param int $count
     * @return $this
     */
    public function setPopularProductsCount($count)
    {
        $this->_popularProductsCount = $count;
        return $this;
    }

    /**
     * Get products count
     *
     * @return int
     */
    public function getPopularProductsCount()
    {
        return $this->_popularProductsCount;
    }

    /**
     * Get collection of best-seller products for the month
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection()
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $connection = $objectManager->get(\Magento\Framework\App\ResourceConnection::class)->getConnection();
            $query = "SELECT MAX(DATE_FORMAT(period, '%Y-%m-%d')) AS period, 
                                         SUM(qty_ordered) AS qty_ordered, 
                                         sales_bestsellers_aggregated_yearly.product_id, 
                                         MAX(product_name) AS product_name, 
                                         MAX(product_price) AS product_price 
                                  FROM sales_bestsellers_aggregated_yearly 
                                  WHERE (sales_bestsellers_aggregated_yearly.product_id IS NOT NULL) 
                                  AND (store_id IN(0)) 
                                  GROUP BY product_id 
                                  ORDER BY qty_ordered DESC 
                                  LIMIT 24";
            $results = $connection->fetchAll($query);
            $productIds = [];

            if (!empty($results)) {
                foreach ($results as $row) {
                    $productIds[] = $row['product_id'];
                }
            }
        } catch (\Exception $e) {
            $productIds = [];
            $bestSellers = $this->_bestSellersCollectionFactory->create()
                ->setPeriod('year');

            foreach ($bestSellers as $product) {
                $productIds[] = $product->getProductId();
            }
        }

        $collection = $this->_productCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['in' => $productIds])
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect('*')
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setPageSize(24)
            ->getSelect()->order(new \Zend_Db_Expr("FIELD(e.entity_id, " . implode(',', $productIds) . ")"));

        return $collection;
    }

    /**
     * Get collection of popular products on product listing
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getPopularProductCollection()
    {
        $productIds = [];
        $bestSellers = $this->_bestSellersCollectionFactory->create()
            ->setPeriod('month');

        foreach ($bestSellers as $product) {
            $productIds[] = $product->getProductId();
        }

        $currentCategory = $this->getCurrentCategory();

        $collection = $this->_productCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['in' => $productIds])
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect('*')
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setPageSize($this->getPopularProductsCount());

        if ($currentCategory instanceof \Magento\Catalog\Model\Category) {
            $collection->addCategoryFilter($currentCategory);
        } else {
            $this->_logger->warning('No current category found, or it is not valid.');
        }
        return $collection;
    }
}
