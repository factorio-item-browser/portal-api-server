<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Command;

use BluePsyduck\TestHelper\ReflectionTrait;
use DateTime;
use DateTimeInterface;
use FactorioItemBrowser\PortalApi\Server\Command\CleanSessionsCommand;
use FactorioItemBrowser\PortalApi\Server\Constant\CommandName;
use FactorioItemBrowser\PortalApi\Server\Repository\UserRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The PHPUnit test of the CleanSessionsCommand class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Command\CleanSessionsCommand
 */
class CleanSessionsCommandTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked user repository.
     * @var UserRepository&MockObject
     */
    protected $userRepository;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $sessionLifeTime = 'abc';

        $command = new CleanSessionsCommand($this->userRepository, $sessionLifeTime);

        $this->assertSame($this->userRepository, $this->extractProperty($command, 'userRepository'));
        $this->assertSame($sessionLifeTime, $this->extractProperty($command, 'sessionLifeTime'));
    }

    /**
     * Tests the configure method.
     * @throws ReflectionException
     * @covers ::configure
     */
    public function testConfigure(): void
    {
        /* @var CleanSessionsCommand&MockObject $command */
        $command = $this->getMockBuilder(CleanSessionsCommand::class)
                        ->onlyMethods(['setName', 'setDescription'])
                        ->setConstructorArgs([$this->userRepository, ''])
                        ->getMock();
        $command->expects($this->once())
                ->method('setName')
                ->with($this->identicalTo(CommandName::CLEAN_SESSIONS));
        $command->expects($this->once())
                ->method('setDescription')
                ->with($this->isType('string'));

        $this->invokeMethod($command, 'configure');
    }

    /**
     * Tests the execute method.
     * @throws ReflectionException
     * @covers ::execute
     */
    public function testExecute(): void
    {
        /* @var DateTime&MockObject $timeCut */
        $timeCut = $this->createMock(DateTime::class);
        /* @var InputInterface&MockObject $input */
        $input = $this->createMock(InputInterface::class);
        /* @var OutputInterface&MockObject $output */
        $output = $this->createMock(OutputInterface::class);

        $this->userRepository->expects($this->once())
                             ->method('cleanupOldSessions')
                             ->with($this->identicalTo($timeCut));

        /* @var CleanSessionsCommand&MockObject $command */
        $command = $this->getMockBuilder(CleanSessionsCommand::class)
                        ->onlyMethods(['calculateTimeCut'])
                        ->setConstructorArgs([$this->userRepository, ''])
                        ->getMock();
        $command->expects($this->once())
                ->method('calculateTimeCut')
                ->willReturn($timeCut);

        $this->invokeMethod($command, 'execute', $input, $output);
    }

    /**
     * Tests the calculateTimeCut method.
     * @throws ReflectionException
     * @covers ::calculateTimeCut
     */
    public function testCalculateTimeCut(): void
    {
        $lifeTimeSession = '+1 day';

        $command = new CleanSessionsCommand($this->userRepository, $lifeTimeSession);
        /* @var DateTimeInterface $result */
        $result = $this->invokeMethod($command, 'calculateTimeCut');

        $this->assertGreaterThanOrEqual(3600, time() - $result->getTimestamp());
    }
}
