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

namespace Elsnertech\DealsCategory\Test\Unit\Cron;

use Elsnertech\DealsCategory\Cron\SyncDealsCategory;
use Elsnertech\DealsCategory\Model\Config;
use Elsnertech\DealsCategory\Model\DealsCategorySync;
use PHPUnit\Framework\TestCase;

class SyncDealsCategoryTest extends TestCase
{
    /**
     * @dataProvider enabledProvider
     */
    public function testSyncRunsOnlyWhenEnabled(bool $enabled, int $expectedRuns): void
    {
        $config = $this->createMock(Config::class);
        $config->method('isEnabled')->willReturn($enabled);
        $sync = $this->createMock(DealsCategorySync::class);
        $sync->expects($this->exactly($expectedRuns))->method('execute');

        (new SyncDealsCategory($config, $sync))->execute();
    }

    /**
     * @return array
     */
    public function enabledProvider(): array
    {
        return [
            'disabled after deploy, until switched on' => [false, 0],
            'enabled' => [true, 1],
        ];
    }
}
