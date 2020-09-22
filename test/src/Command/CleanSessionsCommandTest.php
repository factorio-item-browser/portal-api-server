<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Command;

use BluePsyduck\TestHelper\ReflectionTrait;
use DateTime;
use DateTimeInterface;
use FactorioItemBrowser\PortalApi\Server\Command\CleanSessionsCommand;
use FactorioItemBrowser\PortalApi\Server\Constant\CommandName;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
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
     * The mocked setting repository.
     * @var SettingRepository&MockObject
     */
    protected $settingRepository;

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

        $this->settingRepository = $this->createMock(SettingRepository::class);
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
        $temporarySettingLifeTime = 'def';

        $command = new CleanSessionsCommand(
            $this->settingRepository,
            $this->userRepository,
            $sessionLifeTime,
            $temporarySettingLifeTime,
        );

        $this->assertSame($this->settingRepository, $this->extractProperty($command, 'settingRepository'));
        $this->assertSame($this->userRepository, $this->extractProperty($command, 'userRepository'));
        $this->assertSame($sessionLifeTime, $this->extractProperty($command, 'sessionLifeTime'));
        $this->assertSame($temporarySettingLifeTime, $this->extractProperty($command, 'temporarySettingLifeTime'));
    }

    /**
     * Tests the configure method.
     * @throws ReflectionException
     * @covers ::configure
     */
    public function testConfigure(): void
    {
        $command = $this->getMockBuilder(CleanSessionsCommand::class)
                        ->onlyMethods(['setName', 'setDescription'])
                        ->setConstructorArgs([$this->settingRepository, $this->userRepository, '', ''])
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
        $sessionLifeTime = 'abc';
        $temporarySettingLifeTime = 'def';


        $timeCut1 = $this->createMock(DateTime::class);
        $timeCut2 = $this->createMock(DateTime::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $this->userRepository->expects($this->once())
                             ->method('cleanupOldSessions')
                             ->with($this->identicalTo($timeCut1));
        $this->settingRepository->expects($this->once())
                                ->method('cleanupTemporarySettings')
                                ->with($this->identicalTo($timeCut2));

        $command = $this->getMockBuilder(CleanSessionsCommand::class)
                        ->onlyMethods(['calculateTimeCut'])
                        ->setConstructorArgs([
                            $this->settingRepository,
                            $this->userRepository,
                            $sessionLifeTime,
                            $temporarySettingLifeTime,
                        ])
                        ->getMock();
        $command->expects($this->exactly(2))
                ->method('calculateTimeCut')
                ->withConsecutive(
                    [$this->identicalTo($sessionLifeTime)],
                    [$this->identicalTo($temporarySettingLifeTime)],
                )
                ->willReturnOnConsecutiveCalls(
                    $timeCut1,
                    $timeCut2,
                );

        $this->invokeMethod($command, 'execute', $input, $output);
    }

    /**
     * Tests the calculateTimeCut method.
     * @throws ReflectionException
     * @covers ::calculateTimeCut
     */
    public function testCalculateTimeCut(): void
    {
        $lifeTime = '+1 day';

        $command = new CleanSessionsCommand($this->settingRepository, $this->userRepository, '', '');

        /* @var DateTimeInterface $result */
        $result = $this->invokeMethod($command, 'calculateTimeCut', $lifeTime);

        $this->assertGreaterThanOrEqual(3600, time() - $result->getTimestamp());
    }
}
