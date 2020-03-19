<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Handler\Settings;

use BluePsyduck\TestHelper\ReflectionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\RecipeMode;
use FactorioItemBrowser\PortalApi\Server\Entity\Setting;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Exception\InvalidRequestException;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use FactorioItemBrowser\PortalApi\Server\Exception\UnknownEntityException;
use FactorioItemBrowser\PortalApi\Server\Handler\Settings\SaveHandler;
use FactorioItemBrowser\PortalApi\Server\Helper\SidebarEntitiesHelper;
use FactorioItemBrowser\PortalApi\Server\Transfer\SettingOptionsData;
use JMS\Serializer\SerializerInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionException;

/**
 * The PHPUnit test of the SaveHandler class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Handler\Settings\SaveHandler
 */
class SaveHandlerTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked current user.
     * @var User&MockObject
     */
    protected $currentUser;

    /**
     * The mocked serializer.
     * @var SerializerInterface&MockObject
     */
    protected $serializer;

    /**
     * The mocked sidebar entities helper.
     * @var SidebarEntitiesHelper&MockObject
     */
    protected $sidebarEntitiesHelper;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->currentUser = $this->createMock(User::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->sidebarEntitiesHelper = $this->createMock(SidebarEntitiesHelper::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $handler = new SaveHandler($this->currentUser, $this->serializer, $this->sidebarEntitiesHelper);

        $this->assertSame($this->currentUser, $this->extractProperty($handler, 'currentUser'));
        $this->assertSame($this->serializer, $this->extractProperty($handler, 'serializer'));
        $this->assertSame($this->sidebarEntitiesHelper, $this->extractProperty($handler, 'sidebarEntitiesHelper'));
    }

    /**
     * Tests the handle method.
     * @throws PortalApiServerException
     * @covers ::handle
     */
    public function testHandle(): void
    {
        $settingIdString = 'e61afd17-0c69-4d49-bdf0-a93b416d644a';
        $settingId = Uuid::fromString($settingIdString);
        $locale = 'abc';
        $recipeMode = 'def';

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->identicalTo('setting-id'), $this->identicalTo(''))
                ->willReturn($settingIdString);

        /* @var SettingOptionsData&MockObject $requestOptions */
        $requestOptions = $this->createMock(SettingOptionsData::class);
        $requestOptions->expects($this->once())
                       ->method('getLocale')
                       ->willReturn($locale);
        $requestOptions->expects($this->once())
                       ->method('getRecipeMode')
                       ->willReturn($recipeMode);

        /* @var Setting&MockObject $setting */
        $setting = $this->createMock(Setting::class);
        $setting->expects($this->once())
                ->method('setLocale')
                ->with($this->identicalTo($locale))
                ->willReturnSelf();
        $setting->expects($this->once())
                ->method('setRecipeMode')
                ->with($this->identicalTo($recipeMode))
                ->willReturnSelf();

        $this->currentUser->expects($this->once())
                          ->method('setCurrentSetting')
                          ->with($this->identicalTo($setting));

        $this->sidebarEntitiesHelper->expects($this->once())
                                    ->method('refreshLabels')
                                    ->with($this->identicalTo($setting));

        /* @var SaveHandler&MockObject $handler */
        $handler = $this->getMockBuilder(SaveHandler::class)
                        ->onlyMethods(['parseRequestBody', 'findSetting', 'validateOptions'])
                        ->setConstructorArgs([$this->currentUser, $this->serializer, $this->sidebarEntitiesHelper])
                        ->getMock();
        $handler->expects($this->once())
                ->method('parseRequestBody')
                ->with($this->identicalTo($request))
                ->willReturn($requestOptions);
        $handler->expects($this->once())
                ->method('findSetting')
                ->with($this->equalTo($settingId))
                ->willReturn($setting);
        $handler->expects($this->once())
                ->method('validateOptions')
                ->with($this->identicalTo($requestOptions));

        $result = $handler->handle($request);
        $this->assertInstanceOf(EmptyResponse::class, $result);
    }

    /**
     * Tests the parseRequestBody method.
     * @throws ReflectionException
     * @covers ::parseRequestBody
     */
    public function testParseRequestBody(): void
    {
        $requestBody = 'abc';

        /* @var SettingOptionsData&MockObject $settingOptions */
        $settingOptions = $this->createMock(SettingOptionsData::class);

        /* @var StreamInterface&MockObject $stream */
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->once())
               ->method('getContents')
               ->willReturn($requestBody);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getBody')
                ->willReturn($stream);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($requestBody),
                             $this->identicalTo(SettingOptionsData::class),
                             $this->identicalTo('json')
                         )
                         ->willReturn($settingOptions);

        $handler = new SaveHandler($this->currentUser, $this->serializer, $this->sidebarEntitiesHelper);
        $result = $this->invokeMethod($handler, 'parseRequestBody', $request);

        $this->assertSame($settingOptions, $result);
    }

    /**
     * Tests the parseRequestBody method.
     * @throws ReflectionException
     * @covers ::parseRequestBody
     */
    public function testParseRequestBodyWithException(): void
    {
        $requestBody = 'abc';

        /* @var StreamInterface&MockObject $stream */
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->once())
               ->method('getContents')
               ->willReturn($requestBody);

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getBody')
                ->willReturn($stream);

        $this->serializer->expects($this->once())
                         ->method('deserialize')
                         ->with(
                             $this->identicalTo($requestBody),
                             $this->identicalTo(SettingOptionsData::class),
                             $this->identicalTo('json')
                         )
                         ->willThrowException($this->createMock(Exception::class));

        $this->expectException(InvalidRequestException::class);

        $handler = new SaveHandler($this->currentUser, $this->serializer, $this->sidebarEntitiesHelper);
        $this->invokeMethod($handler, 'parseRequestBody', $request);
    }

    /**
     * Tests the findSetting method.
     * @throws ReflectionException
     * @covers ::findSetting
     */
    public function testFindSetting(): void
    {
        /* @var UuidInterface&MockObject $settingId */
        $settingId = $this->createMock(UuidInterface::class);

        /* @var UuidInterface&MockObject $uuid1 */
        $uuid1 = $this->createMock(UuidInterface::class);
        $uuid1->expects($this->once())
              ->method('compareTo')
              ->with($this->identicalTo($settingId))
              ->willReturn(1);

        /* @var UuidInterface&MockObject $uuid2 */
        $uuid2 = $this->createMock(UuidInterface::class);
        $uuid2->expects($this->once())
              ->method('compareTo')
              ->with($this->identicalTo($settingId))
              ->willReturn(0);

        /* @var Setting&MockObject $setting1 */
        $setting1 = $this->createMock(Setting::class);
        $setting1->expects($this->once())
                 ->method('getId')
                 ->willReturn($uuid1);

        /* @var Setting&MockObject $setting2 */
        $setting2 = $this->createMock(Setting::class);
        $setting2->expects($this->once())
                 ->method('getId')
                 ->willReturn($uuid2);

        $this->currentUser->expects($this->once())
                          ->method('getSettings')
                          ->willReturn(new ArrayCollection([$setting1, $setting2]));

        $handler = new SaveHandler($this->currentUser, $this->serializer, $this->sidebarEntitiesHelper);
        $result = $this->invokeMethod($handler, 'findSetting', $settingId);

        $this->assertSame($setting2, $result);
    }

    /**
     * Tests the findSetting method.
     * @throws ReflectionException
     * @covers ::findSetting
     */
    public function testFindSettingWithoutMatch(): void
    {
        /* @var UuidInterface&MockObject $settingId */
        $settingId = $this->createMock(UuidInterface::class);
        $settingId->expects($this->once())
                  ->method('toString')
                  ->willReturn('abc');

        /* @var UuidInterface&MockObject $uuid1 */
        $uuid1 = $this->createMock(UuidInterface::class);
        $uuid1->expects($this->once())
              ->method('compareTo')
              ->with($this->identicalTo($settingId))
              ->willReturn(1);

        /* @var UuidInterface&MockObject $uuid2 */
        $uuid2 = $this->createMock(UuidInterface::class);
        $uuid2->expects($this->once())
              ->method('compareTo')
              ->with($this->identicalTo($settingId))
              ->willReturn(-1);

        /* @var Setting&MockObject $setting1 */
        $setting1 = $this->createMock(Setting::class);
        $setting1->expects($this->once())
                 ->method('getId')
                 ->willReturn($uuid1);

        /* @var Setting&MockObject $setting2 */
        $setting2 = $this->createMock(Setting::class);
        $setting2->expects($this->once())
                 ->method('getId')
                 ->willReturn($uuid2);

        $this->currentUser->expects($this->once())
                          ->method('getSettings')
                          ->willReturn(new ArrayCollection([$setting1, $setting2]));

        $this->expectException(UnknownEntityException::class);

        $handler = new SaveHandler($this->currentUser, $this->serializer, $this->sidebarEntitiesHelper);
        $this->invokeMethod($handler, 'findSetting', $settingId);
    }

    /**
     * Provides the data for the validateOptions test.
     * @return array<mixed>
     */
    public function provideValidateOptions(): array
    {
        return [
            ['en', RecipeMode::HYBRID, false],
            ['de', RecipeMode::NORMAL, false],
            ['ab-cd', RecipeMode::EXPENSIVE, false],

            ['e', RecipeMode::HYBRID, true],
            ['foobar', RecipeMode::HYBRID, true],

            ['en', 'foo', true],
        ];
    }

    /**
     * Tests the validateOptions method.
     * @param string $locale
     * @param string $recipeMode
     * @param bool $expectException
     * @throws ReflectionException
     * @covers ::validateOptions
     * @dataProvider provideValidateOptions
     */
    public function testValidateOptions(string $locale, string $recipeMode, bool $expectException): void
    {
        /* @var SettingOptionsData&MockObject $options */
        $options = $this->createMock(SettingOptionsData::class);
        $options->expects($this->any())
                ->method('getLocale')
                ->willReturn($locale);
        $options->expects($this->any())
                ->method('getRecipeMode')
                ->willReturn($recipeMode);

        if ($expectException) {
            $this->expectException(InvalidRequestException::class);
        } else {
            $this->addToAssertionCount(1);
        }

        $handler = new SaveHandler($this->currentUser, $this->serializer, $this->sidebarEntitiesHelper);
        $this->invokeMethod($handler, 'validateOptions', $options);
    }
}