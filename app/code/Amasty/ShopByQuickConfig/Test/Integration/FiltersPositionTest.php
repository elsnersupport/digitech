<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Test\Integration;

use Amasty\ShopByQuickConfig\Model\FilterableAttributeList as ModifiedFiltersList;
use Magento\Catalog\Model\Layer\Category\FilterableAttributeList as OriginFiltersList;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test that modified filters list collection return items in same order such as origin filters collection.
 *
 * @magentoAppArea adminhtml
 * @magentoAppIsolation disabled
 * @magentoDbIsolation disabled
 */
class FiltersPositionTest extends TestCase
{
    /**
     * @var ModifiedFiltersList
     */
    private $modifiedFiltersList;

    /**
     * @var OriginFiltersList
     */
    private $originFiltersList;

    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $this->modifiedFiltersList = $objectManager->get(ModifiedFiltersList::class);
        $this->originFiltersList = $objectManager->get(OriginFiltersList::class);
    }

    /**
     * @magentoAppIsolation disabled
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Framework/Search/_files/filterable_attributes.php
     * @magentoConfigFixture default/amshopby/category_filter/enabled 1
     */
    public function testLists(): void
    {
        $modifiedFilters = $this->extractAttributeCodes($this->modifiedFiltersList->getList());
        $originFilters = $this->extractAttributeCodes($this->originFiltersList->getList());

        self::assertSame(
            $modifiedFilters,
            $originFilters,
            'Attributes positions should be the same.'
        );
    }

    /**
     * @param array|\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $filters
     * @return string[]
     */
    private function extractAttributeCodes(iterable $filters): array
    {
        $result = [];
        foreach ($filters as $filter) {
            $result[] = $filter->getAttributeCode();
        }
        
        return $result;
    }
}
