<?php

namespace HyvaElsner\MegaMenu\ViewModel;

use Hyva\Theme\ViewModel\Navigation;
use HyvaElsner\Base\Model\Config as BaseConfig;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Helper\Image;

class Menu extends Navigation
{
    public const CATEGORY_LIST = 'hyva_mega_menu/product_categories/categories_to_show';

    /** @var BaseConfig */
    protected $baseConfig;

    /** @var CategoryCollectionFactory */
    protected CategoryCollectionFactory $categoryCollectionFactory;

    /** @var ProductCollectionFactory */
    protected ProductCollectionFactory $productCollectionFactory;

    /** @var StoreManagerInterface */
    protected StoreManagerInterface $storeManager;

    /** @var Image */
    protected Image $imageHelper;

    /**
     * Constructor
     *
     * @param BaseConfig $baseConfig
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param \Hyva\Theme\Service\Navigation $navigationService
     * @param ProductCollectionFactory $productCollectionFactory
     * @param integer $maxCategoryCacheTags
     */
    public function __construct(
        BaseConfig $baseConfig,
        CategoryCollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager,
        \Hyva\Theme\Service\Navigation $navigationService,
        ProductCollectionFactory $productCollectionFactory,
        Image $imageHelper,
        int $maxCategoryCacheTags = 200
    ) {
        $this->baseConfig = $baseConfig;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelper = $imageHelper;

        parent::__construct($navigationService, $maxCategoryCacheTags);
    }

    /**
     * Get navigation menu items, enriched with menu image.
     *
     * @param int|bool $maxLevel
     * @return array
     */
    public function getNavigation($maxLevel = false): array
    {
        $menuItems = parent::getNavigation($maxLevel);
        $categoryImages = $this->getCategoryImages(['show_in_desktop_menu' => 1]);
        $this->addImagesToMenuItems($menuItems, $categoryImages);
        // $this->addProductsToMenuItems($menuItems);
        $id = 'category-node-' . uniqid();
        $menuItems[$id] = [
            "name" => __("Hot Deals"),
            "id" => $id,
            "url" => "/dealsz",
            "image" => false,
            "has_active" => false,
            "is_active" => false,
            "is_category" => true,
            "is_parent_active" => true,
            "position" => null,
            "path" => "1/2/" . $id,
            "childData" => [],
            "menu_image" => null,
            "show_in_desktop_menu" => "1",
            "show_in_mobile_menu" => "1",
            "products" => $this->productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addFieldToSelect('*')
                ->setPageSize(5)
                ->addFieldToFilter('special_price', ['gt' => 1])
                ->addFieldToFilter('special_to_date', ['gteq' => date('Y-m-d H:i:s')])
                ->getItems()
        ];
        return $menuItems;
    }

    /**
     * Get mobile navigation menu items, enriched with menu image.
     *
     * @param int|bool $maxLevel
     * @return array
     */
    public function getMobileNavigation($maxLevel = false): array
    {
        $menuItems = parent::getNavigation($maxLevel);
        $categoryImages = $this->getCategoryImages(['show_in_mobile_menu' => 1]);
        $this->addImagesToMenuItems($menuItems, $categoryImages);
        $this->addProductsToMenuItems($menuItems);
        return $menuItems;
    }

    /**
     * Fetch category images in bulk based on filters.
     *
     * @param array $filters
     * @return array
     */
    protected function getCategoryImages(array $filters): array
    {
        $categoryCollection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect(['menu_image', 'show_in_desktop_menu', 'show_in_mobile_menu'])
            ->addFieldToFilter(key($filters), current($filters));

        $categoryImages = [];
        foreach ($categoryCollection as $category) {
            $categoryImages[$category->getId()] = [
                'menu_image' => $category->getData('menu_image'),
                'show_in_desktop_menu' => $category->getData('show_in_desktop_menu'),
                'show_in_mobile_menu' => $category->getData('show_in_mobile_menu'),
            ];
        }

        return $categoryImages;
    }

    /**
     * Get categories configured to display products.
     */
    public function getCategoryIds(): array
    {
        try {
            $ids = $this->baseConfig->getConfigValue(
                self::CATEGORY_LIST,
                ScopeInterface::SCOPE_STORE
            );
            return $ids !== null ? explode(',', $ids) : [];
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }

    /**
     * Fetch products for a given category.
     */
    protected function getCategoryProducts(int $categoryId, int $limit = 5)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $category = $this->categoryCollectionFactory->create()
                ->addFieldToFilter('entity_id', $categoryId)
                ->getFirstItem();

            if (!$category || !$category->getId()) {
                return [];
            }

            $productCollection = $this->productCollectionFactory->create()
                ->addAttributeToSelect(['name', 'price', 'small_image'])
                ->addCategoryFilter($category)
                ->addStoreFilter($storeId)
                ->setPageSize($limit)
                ->getItems();

            return $productCollection;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Add images to menu items using pre-fetched category images.
     *
     * @param array $menuItems
     * @param array $categoryImages
     */
    protected function addImagesToMenuItems(array &$menuItems, array $categoryImages): void
    {
        foreach ($menuItems as &$menuItem) {
            $this->addImageToMenuItem($menuItem, $categoryImages);
        }
    }

    /**
     * Recursively add image to a menu item and its children.
     *
     * @param array $menuItem
     * @param array $categoryImages
     */
    protected function addImageToMenuItem(array &$menuItem, array $categoryImages): void
    {
        if (isset($menuItem['id'])) {
            $categoryId = str_replace('category-node-', '', $menuItem['id']);
            if (isset($categoryImages[$categoryId])) {
                $menuItem['menu_image'] = $categoryImages[$categoryId]['menu_image'] ?? null;
                $menuItem['show_in_desktop_menu'] = $categoryImages[$categoryId]['show_in_desktop_menu'] ?? null;
                $menuItem['show_in_mobile_menu'] = $categoryImages[$categoryId]['show_in_mobile_menu'] ?? null;
            }
        }

        if (isset($menuItem['childData'])) {
            foreach ($menuItem['childData'] as &$childMenuItem) {
                $this->addImageToMenuItem($childMenuItem, $categoryImages);
            }
        }
    }

    /**
     * Add products to menu items based on configured categories.
     *
     * @param array $menuItems
     * @return void
     */
    protected function addProductsToMenuItems(array &$menuItems): void
    {
        $categoryIds = $this->getCategoryIds();

        foreach ($menuItems as &$menuItem) {
            if (isset($menuItem['id'])) {
                $categoryId = str_replace('category-node-', '', $menuItem['id']);
                if (in_array($categoryId, $categoryIds)) {
                    $menuItem['products'] = $this->getCategoryProducts((int)$categoryId);
                }
            }

            if (isset($menuItem['childData'])) {
                $this->addProductsToMenuItems($menuItem['childData']);
            }
        }
    }

    /**
     * Get the base URL for category media.
     *
     * @return string
     */
    public function getCategoryMediaUrl(): string
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }
}
