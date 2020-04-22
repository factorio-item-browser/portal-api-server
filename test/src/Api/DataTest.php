<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Api;

use FactorioItemBrowser\Api\Client\ApiClientInterface;
use FactorioItemBrowser\PortalApi\Server\Api\Data;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the Data class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Api\Data
 */
class DataTest extends TestCase
{
    /**
     * Tests the setting and getting the setting.
     * @covers ::getSetting
     * @covers ::setSetting
     */
    public function testSetAndGetSetting(): void
    {
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        $data = new Data();

        $this->assertSame($data, $data->setSetting($setting));
        $this->assertSame($setting, $data->getSetting());
    }

    /**
     * Tests the setting and getting the api client.
     * @covers ::getApiClient
     * @covers ::setApiClient
     */
    public function testSetAndGetApiClient(): void
    {
        /* @var ApiClientInterface&MockObject $apiClient */
        $apiClient = $this->createMock(ApiClientInterface::class);
        $data = new Data();

        $this->assertSame($data, $data->setApiClient($apiClient));
        $this->assertSame($apiClient, $data->getApiClient());
    }

    /**
     * Tests the setting and getting the is fallback.
     * @covers ::getIsFallback
     * @covers ::setIsFallback
     */
    public function testSetAndGetIsFallback(): void
    {
        $data = new Data();

        $this->assertSame($data, $data->setIsFallback(true));
        $this->assertTrue($data->getIsFallback());
    }
}
