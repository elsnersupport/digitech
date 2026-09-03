<?php
/**
 * Elsnertech_DeliveryCountdown
 *
 * @category    Elsnertech
 * @package     Elsnertech_DeliveryCountdown
 * @author      Elsnertech
 * @copyright   Copyright (c) 2025 Elsnertech
 */

namespace Elsnertech\DeliveryCountdown\Test\Unit\Helper;

use Elsnertech\DeliveryCountdown\Helper\Data as DeliveryHelper;
use Elsnertech\DeliveryCountdown\Helper\Location;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LocationTest extends TestCase
{
    /**
     * @dataProvider visitorProvider
     */
    public function testOnlyVisitorsInAnAllowedRegionSeeTheCountdown(
        array $headers,
        array $allowedRegions,
        bool $expected,
        string $description
    ): void {
        $helper = $this->createHelper($headers, ['AE', 'IN'], $allowedRegions);

        $this->assertSame($expected, $helper->isAllowedLocation(), $description);
    }

    /**
     * @return array
     */
    public function visitorProvider(): array
    {
        $dubai = ['CF-IPCountry' => 'AE', 'CF-IPCity' => 'Dubai', 'CF-Region' => 'Dubai', 'CF-RegionCode' => 'DU'];
        $sharjah = [
            'CF-IPCountry' => 'AE',
            'CF-IPCity' => 'Sharjah',
            'CF-Region' => 'Ash Shariqah',
            'CF-RegionCode' => 'SH',
        ];

        return [
            'Dubai is allowed' => [
                $dubai, ['Dubai'], true,
                'The emirate the store delivers to still sees the countdown',
            ],
            'Sharjah is not' => [
                $sharjah, ['Dubai'], false,
                'The reported bug: Sharjah used to be stamped Dubai and let through',
            ],
            'Sharjah allowed when listed' => [
                $sharjah, ['Sharjah'], true,
                'Adding the emirate to the list opens it up',
            ],
            'ISO subdivision spelling also matches' => [
                $sharjah, ['Ash Shariqah'], true,
                'Cloudflare reports the emirate under its romanised ISO name',
            ],
            'hyphenated region code header also matches' => [
                ['CF-IPCountry' => 'AE', 'CF-Region-Code' => 'DU'], ['DU'], true,
                'Cloudflare documents the header hyphenated, so both spellings must work',
            ],
            'region code matches' => [
                $dubai, ['DU'], true,
                'The two letter subdivision code is accepted too',
            ],
            'matching ignores case and padding' => [
                $dubai, ['  dUbAi '], true,
                'Admin input should not have to be typed exactly',
            ],
            'no region filter means the whole country' => [
                $sharjah, [], true,
                'With no allowed regions the country gate alone applies',
            ],
            'country outside the allowed list' => [
                ['CF-IPCountry' => 'US', 'CF-IPCity' => 'Dubai'], ['Dubai'], false,
                'A matching city cannot rescue a country that is not served',
            ],
            'city header missing fails closed' => [
                ['CF-IPCountry' => 'AE'], ['Dubai'], false,
                'Without the Cloudflare location headers the emirate is unknown, '
                . 'and the module must not guess Dubai the way it used to',
            ],
            'Cloudflare cannot place the visitor' => [
                ['CF-IPCountry' => 'XX'], ['Dubai'], false,
                'XX means unknown and must not be treated as a country',
            ],
        ];
    }

    /**
     * @dataProvider apiFallbackProvider
     */
    public function testFallsBackToTheLookupServiceWhenCloudflareOmitsTheCity(
        array $allowedRegions,
        bool $expected,
        string $description
    ): void {
        $dataHelper = $this->createMock(DeliveryHelper::class);
        $dataHelper->method('isLocationCheckEnabled')->willReturn(true);
        $dataHelper->method('getAllowedCountries')->willReturn(['AE']);
        $dataHelper->method('getAllowedRegions')->willReturn($allowedRegions);
        $dataHelper->method('isIpDetectionEnabled')->willReturn(true);
        $dataHelper->method('isFallbackToSessionEnabled')->willReturn(false);
        $dataHelper->method('getGeolocationApiKey')->willReturn(null);
        $dataHelper->method('getPreviewIps')->willReturn([]);

        $curl = $this->createMock(Curl::class);
        $curl->method('getBody')->willReturn(
            '{"country_code":"AE","country_name":"United Arab Emirates",'
            . '"city":"Sharjah","region":"Sharjah","region_code":"SH"}'
        );

        // Country present but no city: exactly what Cloudflare sends before the
        // visitor location headers are switched on.
        $headers = ['CF-IPCountry' => 'AE', 'CF-Connecting-IP' => '2.50.0.1'];

        $helper = $this->buildHelper($dataHelper, $headers, $curl);

        $this->assertSame($expected, $helper->isAllowedLocation(), $description);
    }

    /**
     * @return array
     */
    public function apiFallbackProvider(): array
    {
        return [
            'lookup says Sharjah, only Dubai allowed' => [
                ['Dubai'], false,
                'The fallback still identifies the real emirate and keeps it out',
            ],
            'lookup says Sharjah, Sharjah allowed' => [
                ['Sharjah'], true,
                'So the widget keeps working without the Cloudflare location headers',
            ],
        ];
    }

    /**
     * @dataProvider previewIpProvider
     */
    public function testPreviewAddressesSeeTheCountdownFromAnywhere(
        array $previewIps,
        string $visitorIp,
        bool $expected,
        string $description
    ): void {
        $dataHelper = $this->createMock(DeliveryHelper::class);
        $dataHelper->method('isLocationCheckEnabled')->willReturn(true);
        $dataHelper->method('getAllowedCountries')->willReturn(['AE']);
        $dataHelper->method('getAllowedRegions')->willReturn(['Dubai']);
        $dataHelper->method('isIpDetectionEnabled')->willReturn(true);
        $dataHelper->method('isFallbackToSessionEnabled')->willReturn(false);
        $dataHelper->method('getPreviewIps')->willReturn($previewIps);

        // A developer in Ahmedabad: the wrong country and the wrong city, so only
        // the preview list can let them through.
        $headers = [
            'CF-IPCountry' => 'IN',
            'CF-IPCity' => 'Ahmedabad',
            'CF-Region' => 'Gujarat',
            'CF-Region-Code' => 'GJ',
            'CF-Connecting-IP' => $visitorIp,
        ];

        $this->assertSame(
            $expected,
            $this->buildHelper($dataHelper, $headers)->isAllowedLocation(),
            $description
        );
    }

    /**
     * @return array
     */
    public function previewIpProvider(): array
    {
        return [
            'listed address gets through' => [
                ['180.211.96.18'], '180.211.96.18', true,
                'The developer sees the countdown from their own desk',
            ],
            'unlisted address does not' => [
                ['180.211.96.18'], '180.211.96.19', false,
                'A shopper next door is still outside the delivery area',
            ],
            'range covers a changing office address' => [
                ['180.211.96.0/24'], '180.211.96.77', true,
                'A CIDR range survives a dynamic office IP',
            ],
            'address outside the range' => [
                ['180.211.96.0/24'], '180.211.97.1', false,
                'The range must not leak into the neighbouring block',
            ],
            'empty list changes nothing' => [
                [], '180.211.96.18', false,
                'With no preview addresses the geography decides as before',
            ],
        ];
    }

    public function testLocationCheckCanBeSwitchedOff(): void
    {
        $helper = $this->createHelper(['CF-IPCountry' => 'US'], ['AE'], ['Dubai'], false);

        $this->assertTrue($helper->isAllowedLocation(), 'Disabling the check lets everyone through');
    }

    public function testResultIsResolvedOnlyOnce(): void
    {
        $dataHelper = $this->createMock(DeliveryHelper::class);
        // Called once by the resolver; a second call would mean the work was repeated.
        $dataHelper->expects($this->once())->method('isLocationCheckEnabled')->willReturn(true);
        $dataHelper->method('getAllowedCountries')->willReturn([]);

        $helper = $this->buildHelper($dataHelper, []);

        $helper->isAllowedLocation();
        $helper->isAllowedLocation();
    }

    /**
     * @param array $headers
     * @param string[] $allowedCountries
     * @param string[] $allowedRegions
     * @param bool $checkEnabled
     * @return Location
     */
    private function createHelper(
        array $headers,
        array $allowedCountries,
        array $allowedRegions,
        bool $checkEnabled = true
    ): Location {
        $dataHelper = $this->createMock(DeliveryHelper::class);
        $dataHelper->method('isLocationCheckEnabled')->willReturn($checkEnabled);
        $dataHelper->method('getAllowedCountries')->willReturn($allowedCountries);
        $dataHelper->method('getAllowedRegions')->willReturn($allowedRegions);
        $dataHelper->method('isIpDetectionEnabled')->willReturn(true);
        $dataHelper->method('isFallbackToSessionEnabled')->willReturn(false);
        $dataHelper->method('getGeolocationApiKey')->willReturn(null);
        $dataHelper->method('getPreviewIps')->willReturn([]);

        return $this->buildHelper($dataHelper, $headers);
    }

    /**
     * @param DeliveryHelper $dataHelper
     * @param array $headers
     * @param Curl|null $curl
     * @return Location
     */
    private function buildHelper(DeliveryHelper $dataHelper, array $headers, ?Curl $curl = null): Location
    {
        $context = $this->createMock(Context::class);
        $context->method('getLogger')->willReturn($this->createMock(LoggerInterface::class));

        $request = $this->createMock(HttpRequest::class);
        $request->method('getHeader')->willReturnCallback(function ($name) use ($headers) {
            return $headers[$name] ?? false;
        });

        $remoteAddress = $this->createMock(RemoteAddress::class);
        $remoteAddress->method('getRemoteAddress')->willReturn('');

        return new Location(
            $context,
            $dataHelper,
            $curl ?: $this->createMock(Curl::class),
            $this->createMock(CustomerSession::class),
            $remoteAddress,
            $request
        );
    }
}
