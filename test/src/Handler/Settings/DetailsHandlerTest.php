<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Settings;

use BluePsyduck\TestHelper\ReflectionTrait;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Handler\Settings\DetailsHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\SettingHelper;
use FactorioItemBrowser\PortalApi\Server\Response\TransferResponse;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingDetailsData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use ReflectionException;

/**
 * The PHPUnit test of the DetailsHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Settings\DetailsHandler
 */
class DetailsHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked setting helper.
     * @var SettingHelper&MockObject
     */
    protected $settingHelper;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->settingHelper = $this->createMock(SettingHelper::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new DetailsHandler($this->settingHelper);

        $this->assertSame($this->settingHelper, $this->extractProperty($handler, 'settingHelper'));
    }

    /**
     * Tests the handle method.
     * @throws PortalApiServerException
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $settingIdString = 'a20ef5d4-59bf-48aa-9b72-2e5ddb2f2995';
        $settingId = Uuid::fromString($settingIdString);
        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        /* @var SettingDetailsData&MockObject $settingDetails */
        $settingDetails = $this->createMock(SettingDetailsData::class);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo('setting-id'), $this->identicalTo(''))
                ->willReturn($settingIdString);

        $this->settingHelper->expects($this->once())
                            ->method('findInCurrentUser')
                            ->with($this->equalTo($settingId))
                            ->willReturn($setting);
        $this->settingHelper->expects($this->once())
                            ->method('createSettingDetails')
                            ->with($this->identicalTo($setting))
                            ->willReturn($settingDetails);

        $handler = new DetailsHandler($this->settingHelper);

        /* @var TransferResponse $result */
        $result = $handler->handle($request);

        $this->assertInstanceOf(TransferResponse::class, $result);
        $this->assertSame($settingDetails, $result->getTransfer());
    }
}
