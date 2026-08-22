<?php

namespace Wizzy\Events\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Helper\Data as TexHelper;

class Products implements ObserverInterface
{
    protected $productRepository;
    private $searchCriteriaBuilder;
    private $taxHelper;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        TexHelper $taxHelper
    ) {
        $this->productRepository = $productRepository;
        $this->taxHelper = $taxHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function execute(Observer $observer)
    {
        $data = $observer->getData('data');
        $products = $data->getDataByKey('products');
        $productIds = array_column($products, 'id');

        $productAttributesData = $this->attributesToBeAdded($productIds);

        foreach ($products as $index => &$product) {
            $productId = $product['id'];

            if (isset($productAttributesData[$productId])) {
                $product['attributes'] = array_merge(
                    $product['attributes'] ?? [],
                    $this->addProductAttributes($productAttributesData[$productId], $product)
                );
            }
            $m2Product = $this->productRepository->getById($productId);
            $specialPrice = $m2Product->getPriceInfo()->getPrice('special_price')->getAmount()->getBaseAmount();
            $finalPrice = $m2Product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();
    
            if ($specialPrice !== false && $specialPrice !== 0 && $specialPrice < $finalPrice) {
                $finalPrice = $specialPrice;
            }
            if($finalPrice) {
                $product['sellingPrice'] = $finalPrice;
            }
            if(isset($product['price']) && $product['price'] > $product['finalPrice']) {
                $discount = $product['price'] - $product['finalPrice'];
                $discountPercentage = ($discount / $product['price']) * 100;
                $product['discount'] = $discount;
                $product['discountPercentage'] = round($discountPercentage, 0);
            }
        }

        $data->setData('products', $products);
        return $data;
    }

    private function addProductAttributes($attributesForProduct, $product)
    {
        $attributes = [];
        foreach ($attributesForProduct as $attributeCode => $attributeData) {
            $attributes[] = [
                'id' => $attributeData['code'],
                'name' => $attributeData['name'],
                'values' => [
                    [
                        'value' => [$attributeData['value']],
                        'inStock' => $product['inStock'],
                        'variationId' => $product['id']
                    ]
                ],
                'isSearchable' => false,
                'isFilterable' => true,
                'addInAutocomplete' => false
            ];
        }
        return $attributes;
    }
    private function attributesToBeAdded(array $productIds): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $productIds, 'in')
            ->create();

        $m2Products = $this->productRepository->getList($searchCriteria)->getItems();
        $productAttributesData = [];
        $requiredAttributes = ['pre_order_availability_message' => 0];

        foreach ($m2Products as $m2Product) {
            $attributeData = [];

            foreach ($m2Product->getAttributes() as $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                if (isset($requiredAttributes[$attributeCode]) && $attributeCode !== 'attribute_set_id') {
                    $attributeData[$attributeCode] = [
                        'name' => $attribute->getDefaultFrontendLabel(),
                        'code' => $attributeCode,
                        'value' => $m2Product->getData($attributeCode)
                    ];
                }
            }

            if (!empty($attributeData)) {
                $productAttributesData[$m2Product->getId()] = $attributeData;
            }
        }

        return $productAttributesData;
    }
}