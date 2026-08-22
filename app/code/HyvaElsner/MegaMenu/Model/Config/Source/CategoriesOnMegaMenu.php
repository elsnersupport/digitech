<?php

declare(strict_types=1);

namespace HyvaElsner\MegaMenu\Model\Config\Source;

use Magento\Catalog\Helper\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class CategoriesOnMegaMenu implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Category
     */
    protected $categoryHelper;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;
    
    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * Construct
     *
     * @param Category $catalogCategory
     * @param CategoryFactory $categoryFactory
     * @param CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        Category $catalogCategory,
        CategoryFactory $categoryFactory,
        CollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryHelper = $catalogCategory;
        $this->categoryFactory = $categoryFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * GetStoreCategories function
     *
     * @param boolean $sorted
     * @param boolean $asCollection
     * @param boolean $toLoad
     * @return void
     */
    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->categoryHelper->getStoreCategories($sorted, $asCollection, $toLoad);
    }

    /**
     * GetCategoryCollection function
     *
     * @param boolean $isActive
     * @param boolean $level
     * @param boolean $sortBy
     * @param boolean $pageSize
     * @return void
     */
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
    }

    /**
     * GetParentName function
     *
     * @param string $path
     * @return void
     */
    protected function _getParentName($path = '')
    {
        $parentName = '';
        $rootCats = [1,2];

        $catTree = explode("/", $path);
        // Deleting category itself
        array_pop($catTree);

        if ($catTree && (count($catTree) > count($rootCats))) {
            foreach ($catTree as $catId) {
                if (!in_array($catId, $rootCats)) {
                    $category = $this->categoryFactory->create()->load($catId);
                    $categoryName = $category->getName();
                    $parentName .= $categoryName . ' -> ';
                }
            }
        }

        return $parentName;
    }

    /**
     * ToOptionArray function
     *
     * @return array
     */
    public function toOptionArray()
    {
        $arr = $this->toArray();
        $ret = [];

        foreach ($arr as $key => $value) {

            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $ret;
    }

    /**
     * ToArray function
     *
     * @return array
     */
    public function toArray()
    {
        $categories = $this->getCategoryCollection(true, false, false, false);

        $catagoryList = [];
        foreach ($categories as $category) {
            $catagoryList[$category->getEntityId()] =
            __($this->_getParentName($category->getPath()) . $category->getName());
        }

        return $catagoryList;
    }
}
