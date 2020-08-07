<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Helper;

use BluePsyduck\TestHelper\ReflectionTrait;
use Dflydev\FigCookies\Modifier\SameSite;
use Dflydev\FigCookies\SetCookie;
use FactorioItemBrowser\PortalApi\Server\Entity\User;
use FactorioItemBrowser\PortalApi\Server\Helper\CookieHelper;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use ReflectionException;

/**
 * The PHPUnit test of the CookieHelper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Helper\CookieHelper
 */
class CookieHelperTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $cookieName = 'abc';
        $cookieDomain = 'def';
        $cookiePath = 'ghi';
        $cookieLifeTime = 'jkl';

        $helper = new CookieHelper($cookieName, $cookieDomain, $cookiePath, $cookieLifeTime, true);

        $this->assertSame($cookieName, $this->extractProperty($helper, 'cookieName'));
        $this->assertSame($cookieDomain, $this->extractProperty($helper, 'cookieDomain'));
        $this->assertSame($cookiePath, $this->extractProperty($helper, 'cookiePath'));
        $this->assertSame($cookieLifeTime, $this->extractProperty($helper, 'cookieLifeTime'));
        $this->assertTrue($this->extractProperty($helper, 'useSecureCookie'));
    }

    /**
     * Tests the readUserId method.
     * @covers ::readUserId
     */
    public function testReadUserId(): void
    {
        $cookieName = 'abc';
        $id = 'bdec7a85-5de5-49b9-9634-8b12319fa212';

        $request = new ServerRequest();
        $request = $request->withHeader('Cookie', "{$cookieName}={$id}");

        $helper = new CookieHelper($cookieName, '', '', '', false);
        $result = $helper->readUserId($request);

        $this->assertEquals(Uuid::fromString($id), $result);
    }

    /**
     * Tests the readUserId method.
     * @covers ::readUserId
     */
    public function testReadUserIdWithException(): void
    {
        $cookieName = 'foo';
        $id = 'xyz';

        $request = new ServerRequest();
        $request = $request->withHeader('Cookie', "{$cookieName}={$id}");

        $helper = new CookieHelper($cookieName, '', '', '', false);
        $result = $helper->readUserId($request);

        $this->assertNull($result);
    }

    /**
     * Tests the injectUser method.
     * @throws ReflectionException
     * @covers ::injectUser
     */
    public function testInjectUser(): void
    {
        $cookie = SetCookie::create('foo', 'bar');
        $response = new Response();

        $user = $this->createMock(User::class);

        $helper = $this->getMockBuilder(CookieHelper::class)
                       ->onlyMethods(['createCookie'])
                       ->setConstructorArgs(['', '', '', '', false])
                       ->getMock();
        $helper->expects($this->once())
               ->method('createCookie')
               ->with($this->identicalTo($user))
               ->willReturn($cookie);

        /* @var ResponseInterface $result*/
        $result = $this->invokeMethod($helper, 'injectUser', $response, $user);

        $headerLine = $result->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString('foo', $headerLine);
        $this->assertStringContainsString('bar', $headerLine);
    }

    /**
     * Tests the createCookie method.
     * @throws ReflectionException
     * @covers ::createCookie
     */
    public function testCreateCookie(): void
    {
        $cookieName = 'abc';
        $cookieDomain = 'def';
        $cookiePath = 'ghi';
        $cookieLifeTime = '+1 hour';
        $useSecureCookie = true;

        $userIdString = 'bdec7a85-5de5-49b9-9634-8b12319fa212';
        $userId = Uuid::fromString($userIdString);

        $user = $this->createMock(User::class);
        $user->expects($this->once())
             ->method('getId')
             ->willReturn($userId);

        $helper = new CookieHelper(
            $cookieName,
            $cookieDomain,
            $cookiePath,
            $cookieLifeTime,
            $useSecureCookie,
        );
        /* @var SetCookie $result */
        $result = $this->invokeMethod($helper, 'createCookie', $user);

        $this->assertSame($cookieName, $result->getName());
        $this->assertSame($userIdString, $result->getValue());
        $this->assertSame($cookieDomain, $result->getDomain());
        $this->assertSame($cookiePath, $result->getPath());
        $this->assertLessThanOrEqual(time() + 3600, $result->getExpires());
        $this->assertTrue($result->getSecure());
        $this->assertTrue($result->getHttpOnly());
        $this->assertEquals(SameSite::strict(), $result->getSameSite());
    }
}
