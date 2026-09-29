<?php
/**
 * Elsnertech_DealsCategory
 *
 * @category    Elsnertech
 * @package     Elsnertech_DealsCategory
 * @author      Elsnertech
 * @copyright   Copyright (c) 2026 Elsnertech
 */
declare(strict_types=1);

namespace Elsnertech\DealsCategory\Model;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatusResource;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Finds the products that currently count as a deal.
 *
 * Same rules as the old pub/deal_products_assign.php widget conditions (enabled, visible in
 * catalog, in stock, special_price >= 1, special_to_date set), except the special price must
 * actually be running today: the old script compared against a fixed 2024-11-09 and ignored
 * special_from_date, so expired and not-yet-started deals stayed in the category.
 */
class DealProductProvider
{
    private const MIN_SPECIAL_PRICE = 1;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * @var StockStatusResource
     */
    private $stockStatusResource;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Visibility $visibility
     * @param StockStatusResource $stockStatusResource
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Visibility $visibility,
        StockStatusResource $stockStatusResource,
        TimezoneInterface $timezone
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->visibility = $visibility;
        $this->stockStatusResource = $stockStatusResource;
        $this->timezone = $timezone;
    }

    /**
     * IDs of the products whose special price is active today in the given store view.
     *
     * @param int $storeId
     * @return int[]
     */
    public function getProductIds(int $storeId): array
    {
        // Special price dates are whole days in the store's timezone, inclusive at both ends.
        $today = $this->timezone->scopeDate($storeId)->format('Y-m-d');

        $collection = $this->collectionFactory->create();
        $collection->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->setVisibility($this->visibility->getVisibleInCatalogIds())
            ->addAttributeToFilter('status', ['eq' => Status::STATUS_ENABLED])
            ->addAttributeToFilter('special_price', ['gteq' => self::MIN_SPECIAL_PRICE])
            ->addAttributeToFilter('special_to_date', ['gteq' => $today . ' 00:00:00'])
            ->addAttributeToFilter(
                'special_from_date',
                [['null' => true], ['lteq' => $today . ' 23:59:59']],
                'left'
            );
        // Always require stock, whatever "Display Out of Stock Products" is set to.
        $this->stockStatusResource->addStockDataToCollection($collection, true);

        return array_values(array_unique(array_map('intval', $collection->getAllIds())));
    }
}
