<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Response;

use BluePsyduck\TestHelper\ReflectionTrait;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Response\ErrorResponseGenerator;
use FactorioItemBrowser\PortalApi\Server\Exception\PortalApiServerException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Log\LoggerInterface;

/**
 * The PHPUnit test of the ErrorResponseGenerator class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Response\ErrorResponseGenerator
 */
class ErrorResponseGeneratorTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked logger.
     * @var LoggerInterface&MockObject
     */
    protected $errorLogger;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->errorLogger = $this->createMock(LoggerInterface::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $generator = new ErrorResponseGenerator($this->errorLogger, true);

        $this->assertSame($this->errorLogger, $this->extractProperty($generator, 'errorLogger'));
        $this->assertTrue($this->extractProperty($generator, 'isDebug'));
    }

    /**
     * Tests the invoking.
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $exception = new Exception();
        $expectedStatusCode = 500;
        $expectedMessage = 'Internal server error.';
        $responseError = [
            'abc' => 'def',
        ];
        $expectedPayload = [
            'error' => [
                'abc' => 'def',
            ],
        ];

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /* @var ErrorResponseGenerator&MockObject $generator */
        $generator = $this->getMockBuilder(ErrorResponseGenerator::class)
                          ->onlyMethods(['logException', 'createResponseError'])
                          ->setConstructorArgs([$this->errorLogger, true])
                          ->getMock();
        $generator->expects($this->once())
                  ->method('logException')
                  ->with($this->identicalTo($expectedStatusCode), $this->identicalTo($exception));
        $generator->expects($this->once())
                  ->method('createResponseError')
                  ->with($this->identicalTo($expectedMessage), $this->identicalTo($exception))
                  ->willReturn($responseError);

        $result = $generator($exception, $request, $response);

        $this->assertInstanceOf(JsonResponse::class, $result);
        /* @var JsonResponse $result */
        $this->assertEquals($expectedPayload, $result->getPayload());
    }

    /**
     * Tests the invoking with an ApiServerException.
     * @covers ::__invoke
     */
    public function testInvokeWithApiServerException(): void
    {
        $exception = new PortalApiServerException('foo', 123);
        $expectedStatusCode = 123;
        $expectedMessage = 'foo';
        $responseError = [
            'abc' => 'def',
        ];
        $expectedPayload = [
            'error' => [
                'abc' => 'def',
            ],
        ];

        /* @var ServerRequestInterface&MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /* @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /* @var ErrorResponseGenerator&MockObject $generator */
        $generator = $this->getMockBuilder(ErrorResponseGenerator::class)
                          ->onlyMethods(['logException', 'createResponseError'])
                          ->setConstructorArgs([$this->errorLogger, true])
                          ->getMock();
        $generator->expects($this->once())
                  ->method('logException')
                  ->with($this->identicalTo($expectedStatusCode), $this->identicalTo($exception));
        $generator->expects($this->once())
                  ->method('createResponseError')
                  ->with($this->identicalTo($expectedMessage), $this->identicalTo($exception))
                  ->willReturn($responseError);

        $result = $generator($exception, $request, $response);

        $this->assertInstanceOf(JsonResponse::class, $result);
        /* @var JsonResponse $result */
        $this->assertEquals($expectedPayload, $result->getPayload());
    }

    /**
     * Tests the logException method.
     * @throws ReflectionException
     * @covers ::logException
     */
    public function testLogException(): void
    {
        $statusCode = 500;
        /* @var Exception&MockObject $exception */
        $exception = $this->createMock(Exception::class);

        $this->errorLogger->expects($this->once())
                     ->method('crit')
                     ->with($this->identicalTo($exception));

        $generator = new ErrorResponseGenerator($this->errorLogger, true);
        $this->invokeMethod($generator, 'logException', $statusCode, $exception);
    }

    /**
     * Tests the logException method with a statusCode outside the logged range.
     * @throws ReflectionException
     * @covers ::logException
     */
    public function testLogExceptionWithInvalidStatusCode(): void
    {
        $statusCode = 400;
        /* @var Exception&MockObject $exception */
        $exception = $this->createMock(Exception::class);

        $this->errorLogger->expects($this->never())
                     ->method('crit');

        $generator = new ErrorResponseGenerator($this->errorLogger, true);
        $this->invokeMethod($generator, 'logException', $statusCode, $exception);
    }

    /**
     * Tests the createResponseError method with debug mode.
     * @throws ReflectionException
     * @covers ::createResponseError
     */
    public function testCreateResponseErrorWithDebug(): void
    {
        $message = 'abc';
        $exceptionMessage = 'def';
        $exception = new Exception($exceptionMessage);

        $generator = new ErrorResponseGenerator($this->errorLogger, true);
        $result = $this->invokeMethod($generator, 'createResponseError', $message, $exception);

        $this->assertArrayHasKey('message', $result);
        $this->assertSame($exceptionMessage, $result['message']);
        $this->assertArrayHasKey('backtrace', $result);
        $this->assertIsArray($result['backtrace']);
    }

    /**
     * Tests the createResponseError method without debug mode.
     * @throws ReflectionException
     * @covers ::createResponseError
     */
    public function testCreateResponseErrorWithoutDebug(): void
    {
        $message = 'abc';
        $exceptionMessage = 'def';
        $exception = new Exception($exceptionMessage);
        $expectedResult = [
            'message' => 'abc',
        ];

        $generator = new ErrorResponseGenerator($this->errorLogger, false);
        $result = $this->invokeMethod($generator, 'createResponseError', $message, $exception);

        $this->assertEquals($expectedResult, $result);
    }
}
