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

namespace Elsnertech\DealsCategory\Test\Unit\Model;

use Elsnertech\DealsCategory\Model\DealProductProvider;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatusResource;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\TestCase;

class DealProductProviderTest extends TestCase
{
    private const STORE_ID = 1;

    /**
     * @var array<string, array>
     */
    private $filters = [];

    public function testOnlySpecialPricesRunningTodayQualify(): void
    {
        $this->provider(['5', '7'])->getProductIds(self::STORE_ID);

        $this->assertSame(['gteq' => 1], $this->filters['special_price'][0]);
        $this->assertSame(
            ['gteq' => '2026-09-25 00:00:00'],
            $this->filters['special_to_date'][0],
            'A deal ending today still counts; the old fixed 2024-11-09 kept expired deals'
        );
        $this->assertSame(
            [[['null' => true], ['lteq' => '2026-09-25 23:59:59']], 'left'],
            $this->filters['special_from_date'],
            'Deals without a start date, or starting today or earlier, count; future ones do not'
        );
        $this->assertSame(['eq' => 1], $this->filters['status'][0]);
    }

    public function testReturnsUniqueIntegerIds(): void
    {
        $this->assertSame([5, 7], $this->provider(['5', '7', '5'])->getProductIds(self::STORE_ID));
    }

    /**
     * @param string[] $allIds
     * @return DealProductProvider
     */
    private function provider(array $allIds): DealProductProvider
    {
        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())->method('setStoreId')->with(self::STORE_ID)->willReturnSelf();
        $collection->expects($this->once())->method('addStoreFilter')->with(self::STORE_ID)->willReturnSelf();
        $collection->expects($this->once())->method('setVisibility')->with([2, 4])->willReturnSelf();
        $collection->method('addAttributeToFilter')->willReturnCallback(
            function ($attribute, ...$args) use ($collection) {
                $this->filters[$attribute] = $args;
                return $collection;
            }
        );
        $collection->method('getAllIds')->willReturn($allIds);

        $collectionFactory = $this->createMock(CollectionFactory::class);
        $collectionFactory->method('create')->willReturn($collection);

        $visibility = $this->createMock(Visibility::class);
        $visibility->method('getVisibleInCatalogIds')->willReturn([2, 4]);

        $stockStatusResource = $this->createMock(StockStatusResource::class);
        $stockStatusResource->expects($this->once())
            ->method('addStockDataToCollection')
            ->with($collection, true);

        $timezone = $this->createMock(TimezoneInterface::class);
        $timezone->method('scopeDate')
            ->with(self::STORE_ID)
            ->willReturn(new \DateTime('2026-09-25 00:00:00', new \DateTimeZone('Asia/Dubai')));

        return new DealProductProvider($collectionFactory, $visibility, $stockStatusResource, $timezone);
    }
}
