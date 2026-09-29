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

use Elsnertech\DealsCategory\Model\Config;
use Elsnertech\DealsCategory\Model\DealProductProvider;
use Elsnertech\DealsCategory\Model\DealsCategorySync;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DealsCategorySyncTest extends TestCase
{
    private const CATEGORY_ID = 144;
    private const STORE_ID = 1;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var DealProductProvider|MockObject
     */
    private $dealProductProvider;

    /**
     * @var Category|MockObject
     */
    private $category;

    /**
     * @var CategoryResource|MockObject
     */
    private $categoryResource;

    /**
     * @var DealsCategorySync
     */
    private $sync;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->config->method('getCategoryId')->willReturn(self::CATEGORY_ID);

        $this->dealProductProvider = $this->createMock(DealProductProvider::class);

        $this->category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setStoreId', 'load', 'getId', 'save'])
            ->addMethods(['setPostedProducts'])
            ->getMock();
        $this->category->method('load')->willReturnSelf();
        $this->category->method('getId')->willReturn(self::CATEGORY_ID);

        $categoryFactory = $this->createMock(CategoryFactory::class);
        $categoryFactory->method('create')->willReturn($this->category);

        $this->categoryResource = $this->createMock(CategoryResource::class);

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(self::STORE_ID);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getDefaultStoreView')->willReturn($store);

        $this->sync = new DealsCategorySync(
            $this->config,
            $this->dealProductProvider,
            $categoryFactory,
            $this->categoryResource,
            $storeManager,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function testUnchangedDealsDoNotSaveTheCategory(): void
    {
        $this->givenDeals([12, 10, 11]);
        $this->givenCategoryPositions(['10' => '1', '11' => '1', '12' => '1']);

        $this->category->expects($this->never())->method('setPostedProducts');
        $this->category->expects($this->never())->method('save');

        $result = $this->sync->execute();

        $this->assertFalse($result->hasChanges());
        $this->assertFalse($result->isSaved(), 'Saving every run is what flushed the page cache every 5 minutes');
    }

    public function testNewDealsAreAddedExpiredOnesRemovedAndExistingPositionsKept(): void
    {
        $this->givenDeals([10, 12, 13]);
        $this->givenCategoryPositions(['10' => '5', '11' => '1', '12' => '2']);

        $this->category->expects($this->once())
            ->method('setPostedProducts')
            ->with([10 => 5, 12 => 2, 13 => 1]);
        $this->category->expects($this->once())->method('save');

        $result = $this->sync->execute();

        $this->assertSame([13], $result->getAddedProductIds());
        $this->assertSame([11], $result->getRemovedProductIds());
        $this->assertTrue($result->isSaved());
    }

    public function testCategoryIsEmptiedWhenNoProductQualifies(): void
    {
        $this->givenDeals([]);
        $this->givenCategoryPositions(['10' => '1', '11' => '1']);

        $this->category->expects($this->once())->method('setPostedProducts')->with([]);
        $this->category->expects($this->once())->method('save');

        $result = $this->sync->execute();

        $this->assertSame([10, 11], $result->getRemovedProductIds());
    }

    public function testDryRunReportsChangesWithoutSaving(): void
    {
        $this->givenDeals([10, 13]);
        $this->givenCategoryPositions(['10' => '1', '11' => '1']);

        $this->category->expects($this->never())->method('setPostedProducts');
        $this->category->expects($this->never())->method('save');

        $result = $this->sync->execute(true);

        $this->assertSame([13], $result->getAddedProductIds());
        $this->assertSame([11], $result->getRemovedProductIds());
        $this->assertFalse($result->isSaved());
    }

    public function testCategoryIsLoadedAndSavedInAdminScope(): void
    {
        $this->givenDeals([10]);
        $this->givenCategoryPositions([]);

        // Store 0: saving in store view 1 wrote store-level values and Wizzy skipped the sync.
        $this->category->expects($this->once())->method('setStoreId')->with(0);
        $this->category->expects($this->once())->method('load')->with(self::CATEGORY_ID);

        $this->sync->execute();
    }

    public function testDealsAreEvaluatedForTheDefaultStoreView(): void
    {
        $this->givenCategoryPositions([]);
        $this->dealProductProvider->expects($this->once())
            ->method('getProductIds')
            ->with(self::STORE_ID)
            ->willReturn([]);

        $this->sync->execute();
    }

    public function testFailsWhenNoCategoryIsConfigured(): void
    {
        $config = $this->createMock(Config::class);
        $config->method('getCategoryId')->willReturn(null);
        $sync = new DealsCategorySync(
            $config,
            $this->dealProductProvider,
            $this->createMock(CategoryFactory::class),
            $this->categoryResource,
            $this->createMock(StoreManagerInterface::class),
            $this->createMock(LoggerInterface::class)
        );

        $this->expectException(LocalizedException::class);
        $this->dealProductProvider->expects($this->never())->method('getProductIds');

        $sync->execute();
    }

    public function testFailsWhenTheCategoryDoesNotExist(): void
    {
        $missing = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setStoreId', 'load', 'getId', 'save'])
            ->getMock();
        $missing->method('load')->willReturnSelf();
        $missing->method('getId')->willReturn(null);
        $missing->expects($this->never())->method('save');
        $categoryFactory = $this->createMock(CategoryFactory::class);
        $categoryFactory->method('create')->willReturn($missing);

        $sync = new DealsCategorySync(
            $this->config,
            $this->dealProductProvider,
            $categoryFactory,
            $this->categoryResource,
            $this->createMock(StoreManagerInterface::class),
            $this->createMock(LoggerInterface::class)
        );

        $this->expectException(NoSuchEntityException::class);

        $sync->execute();
    }

    /**
     * @param int[] $productIds
     */
    private function givenDeals(array $productIds): void
    {
        $this->dealProductProvider->method('getProductIds')->willReturn($productIds);
    }

    /**
     * Shaped like CategoryResource::getProductsPosition(): fetchPairs() rows as strings.
     *
     * @param array<string, string> $positions
     */
    private function givenCategoryPositions(array $positions): void
    {
        $this->categoryResource->method('getProductsPosition')->with($this->category)->willReturn($positions);
    }
}
