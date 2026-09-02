<?php
/**
 * Elsnertech_DeliveryCountdown
 *
 * @category    Elsnertech
 * @package     Elsnertech_DeliveryCountdown
 * @author      Elsnertech
 * @copyright   Copyright (c) 2025 Elsnertech
 */

namespace Elsnertech\DeliveryCountdown\Test\Unit\ViewModel;

use Elsnertech\DeliveryCountdown\Helper\Data as DeliveryHelper;
use Elsnertech\DeliveryCountdown\Helper\Location as LocationHelper;
use Elsnertech\DeliveryCountdown\ViewModel\DeliveryCountdown;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DeliveryCountdownTest extends TestCase
{
    private const TIMEZONE = 'Asia/Dubai';
    private const CUTOFF = '14:00';

    /**
     * Saturday and Sunday, the store's configured non-working days.
     */
    private const WEEKEND = [0, 6];

    /**
     * @dataProvider deliveryStateProvider
     */
    public function testDeliveryStateRespectsNonWorkingDays(
        string $now,
        bool $expectedSameDay,
        string $expectedDeliveryDate,
        string $description
    ): void {
        $info = $this->createViewModel($now)->getDeliveryInfo();

        $this->assertSame($expectedSameDay, $info['is_same_day'], $description);
        $this->assertSame($expectedDeliveryDate, $info['delivery_date'], $description);
    }

    /**
     * @return array
     */
    public function deliveryStateProvider(): array
    {
        return [
            // 2026-09-05 is a Saturday, 2026-09-07 a Monday, 2026-09-11 a Friday.
            'Saturday before cutoff is not a delivery day' => [
                '2026-09-05 10:00:00', false, '2026-09-07',
                'Saturday is non-working, so same day must not be offered',
            ],
            'Sunday before cutoff is not a delivery day' => [
                '2026-09-06 10:00:00', false, '2026-09-07',
                'Sunday morning used to claim same day delivery while dating it Monday',
            ],
            'Sunday after cutoff' => [
                '2026-09-06 16:00:00', false, '2026-09-07',
                'Sunday afternoon delivers on Monday',
            ],
            'Monday before cutoff qualifies for same day' => [
                '2026-09-07 10:00:00', true, '2026-09-07',
                'A working day before the cutoff is the one same day case',
            ],
            'Monday after cutoff rolls to Tuesday' => [
                '2026-09-07 16:00:00', false, '2026-09-08',
                'Past the cutoff the delivery moves to the next working day',
            ],
            'Friday before cutoff qualifies for same day' => [
                '2026-09-11 10:00:00', true, '2026-09-11',
                'Friday is a working day for this store',
            ],
            'Friday after cutoff skips the weekend' => [
                '2026-09-11 16:00:00', false, '2026-09-14',
                'Friday evening must skip Saturday and Sunday and land on Monday',
            ],
        ];
    }

    public function testCountdownTargetsTheNextWorkingDayCutoff(): void
    {
        // Sunday 10:00, so the next chance to order is Monday at 14:00: 28 hours away.
        $info = $this->createViewModel('2026-09-06 10:00:00')->getDeliveryInfo();

        $this->assertSame(28, $info['hours_remaining']);
        $this->assertSame(0, $info['minutes_remaining']);
    }

    public function testCountdownTargetsTodaysCutoffOnAWorkingDay(): void
    {
        // Monday 10:30, so today's 14:00 cutoff is 3 hours 30 minutes away.
        $info = $this->createViewModel('2026-09-07 10:30:00')->getDeliveryInfo();

        $this->assertSame(3, $info['hours_remaining']);
        $this->assertSame(30, $info['minutes_remaining']);
    }

    public function testMessagePlaceholdersAreSubstituted(): void
    {
        $info = $this->createViewModel('2026-09-06 10:00:00')->getDeliveryInfo();

        $this->assertSame('Delivery By, 07 Sep.', $info['delivery_message']);
    }

    /**
     * @dataProvider dayPlaceholderProvider
     */
    public function testDayPlaceholderTracksHowFarOffTheDeliveryIs(
        string $now,
        string $expected,
        string $description
    ): void {
        $viewModel = $this->createViewModel(
            $now,
            self::WEEKEND,
            true,
            self::CUTOFF,
            'FREE delivery {day}, {date}.'
        );

        $this->assertSame($expected, $viewModel->getDeliveryInfo()['delivery_message'], $description);
    }

    /**
     * @return array
     */
    public function dayPlaceholderProvider(): array
    {
        return [
            'Monday evening delivers the very next day' => [
                '2026-09-07 16:00:00', 'FREE delivery Tomorrow, 08 Sep.',
                'Tuesday is a working day, so the parcel really does arrive tomorrow',
            ],
            'Friday evening is pushed past the weekend' => [
                '2026-09-11 16:00:00', 'FREE delivery on, 14 Sep.',
                'Once the weekend intervenes the wording drops "Tomorrow"',
            ],
            'Sunday morning still lands on Monday' => [
                '2026-09-06 10:00:00', 'FREE delivery Tomorrow, 07 Sep.',
                'Sunday is non-working but Monday is genuinely tomorrow',
            ],
        ];
    }

    public function testEveryDayDeliversWhenTheExclusionIsOff(): void
    {
        $info = $this->createViewModel('2026-09-06 10:00:00', [])->getDeliveryInfo();

        $this->assertTrue($info['is_same_day'], 'With no non-working days Sunday delivers same day');
        $this->assertSame('2026-09-06', $info['delivery_date']);
    }

    public function testSameDayNeverOffersWhenDisabled(): void
    {
        $info = $this->createViewModel('2026-09-07 10:00:00', self::WEEKEND, false)->getDeliveryInfo();

        $this->assertFalse($info['is_same_day']);
        $this->assertSame('2026-09-08', $info['delivery_date']);
    }

    public function testWidgetHidesWhenTheCutoffIsMalformed(): void
    {
        $viewModel = $this->createViewModel('2026-09-07 10:00:00', self::WEEKEND, true, 'not a time');

        $this->assertFalse($viewModel->isEnabled());
        $this->assertSame([], $viewModel->getDeliveryInfo());
    }

    public function testJsConfigCarriesRulesRatherThanADecision(): void
    {
        $config = $this->createViewModel('2026-09-06 10:00:00')->getJsConfig();

        // Nothing render-time may leak into the markup, or the full page cache
        // would serve yesterday's answer.
        $this->assertSame(14, $config['cutoffHour']);
        $this->assertSame(0, $config['cutoffMinute']);
        $this->assertSame(self::WEEKEND, $config['nonWorkingDays']);
        $this->assertSame(self::TIMEZONE, $config['timezone']);
        $this->assertArrayNotHasKey('is_same_day', $config);
        $this->assertArrayNotHasKey('cutoff_timestamp', $config);
    }

    /**
     * @param string $now
     * @param int[] $nonWorkingDays
     * @param bool $sameDayEnabled
     * @param string $cutoff
     * @param string $nextDayMessage
     * @return DeliveryCountdown
     */
    private function createViewModel(
        string $now,
        array $nonWorkingDays = self::WEEKEND,
        bool $sameDayEnabled = true,
        string $cutoff = self::CUTOFF,
        string $nextDayMessage = 'Delivery By, {date}.'
    ): DeliveryCountdown {
        $deliveryHelper = $this->createMock(DeliveryHelper::class);
        $deliveryHelper->method('isEnabled')->willReturn(true);
        $deliveryHelper->method('getCutoffTime')->willReturn($cutoff);
        $deliveryHelper->method('isSameDayEnabled')->willReturn($sameDayEnabled);
        $deliveryHelper->method('getNonWorkingDays')->willReturn($nonWorkingDays);
        $deliveryHelper->method('getSameDayMessage')->willReturn('Delivery Today');
        $deliveryHelper->method('getNextDayMessage')->willReturn($nextDayMessage);

        $locationHelper = $this->createMock(LocationHelper::class);
        $locationHelper->method('isAllowedLocation')->willReturn(true);

        $timezone = $this->createMock(TimezoneInterface::class);
        $timezone->method('date')->willReturnCallback(function () use ($now) {
            return new \DateTime($now, new \DateTimeZone(self::TIMEZONE));
        });
        $timezone->method('getConfigTimezone')->willReturn(self::TIMEZONE);

        return new DeliveryCountdown(
            $deliveryHelper,
            $locationHelper,
            $timezone,
            $this->createMock(LoggerInterface::class)
        );
    }
}
