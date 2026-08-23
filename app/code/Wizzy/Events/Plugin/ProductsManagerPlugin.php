<?php

namespace Wizzy\Events\Plugin;

use DateTime;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Wizzy\Search\Services\Catalogue\ProductsManager;

/**
 * Match special price start/end by calendar day so API values like
 * "Y-m-d 23:59:59" are queued the same way as admin "Y-m-d 00:00:00".
 */
class ProductsManagerPlugin
{
    private const MAX_PRODUCTS_TO_FETCH = 10000;

    public function __construct(
        private CollectionFactory $productCollectionFactory
    ) {
    }

    /**
     * @param ProductsManager $subject
     * @param callable $proceed
     * @return array
     */
    public function aroundGetUpdatedSpecialPriceProductIds(
        ProductsManager $subject,
        callable $proceed
    ): array {
        $now = new DateTime();
        $yesterday = (clone $now)->modify('-1 day');

        $page = 1;
        $productIds = [];

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('special_from_date')
            ->addAttributeToSelect('special_to_date')
            ->addAttributeToFilter([
                [
                    'attribute' => 'special_from_date',
                    'from' => $now->format('Y-m-d 00:00:00'),
                    'to' => $now->format('Y-m-d 23:59:59'),
                ],
                [
                    'attribute' => 'special_to_date',
                    'from' => $yesterday->format('Y-m-d 00:00:00'),
                    'to' => $yesterday->format('Y-m-d 23:59:59'),
                ],
            ])
            ->setPageSize(self::MAX_PRODUCTS_TO_FETCH);

        while (true) {
            $collection->setCurPage($page);
            $pageProductIds = $collection->getAllIds();
            $productIds = array_merge($productIds, $pageProductIds);
            $page++;

            if (count($pageProductIds) < self::MAX_PRODUCTS_TO_FETCH) {
                break;
            }
        }

        return $productIds;
    }
}
