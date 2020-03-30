<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Helper;

use BluePsyduck\TestHelper\ReflectionTrait;
use DateTime;
use Exception;
use FactorioItemBrowser\Api\Client\Entity\ExportJob;
use FactorioItemBrowser\Api\Client\Response\Combination\CombinationStatusResponse;
use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Repository\CombinationRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionException;

/**
 * The PHPUnit test of the CombinationHelper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper
 */
class CombinationHelperTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked combination repository.
     * @var CombinationRepository&MockObject
     */
    protected $combinationRepository;

    /**
     * Sets up the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->combinationRepository = $this->createMock(CombinationRepository::class);
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $helper = new CombinationHelper($this->combinationRepository);

        $this->assertSame($this->combinationRepository, $this->extractProperty($helper, 'combinationRepository'));
    }

    /**
     * Tests the createCombinationFromStatusResponse method.
     * @throws Exception
     * @covers ::createCombinationFromStatusResponse
     */
    public function testCreateCombinationFromStatusResponse(): void
    {
        $combinationIdString = 'af1da41d-55db-4075-9a34-1ee405a7c683';
        $modNames = ['abc', 'def'];

        $combinationId = Uuid::fromString($combinationIdString);

        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        /* @var CombinationStatusResponse&MockObject $statusResponse */
        $statusResponse = $this->createMock(CombinationStatusResponse::class);
        $statusResponse->expects($this->once())
                       ->method('getId')
                       ->willReturn($combinationIdString);
        $statusResponse->expects($this->once())
                       ->method('getModNames')
                       ->willReturn($modNames);

        $this->combinationRepository->expects($this->once())
                                    ->method('getCombination')
                                    ->with($this->equalTo($combinationId))
                                    ->willReturn(null);

        /* @var CombinationHelper&MockObject $helper */
        $helper = $this->getMockBuilder(CombinationHelper::class)
                       ->onlyMethods(['createCombination', 'hydrateStatusResponseToCombination'])
                       ->setConstructorArgs([$this->combinationRepository])
                       ->getMock();
        $helper->expects($this->once())
               ->method('createCombination')
               ->with($this->equalTo($combinationId), $this->identicalTo($modNames))
               ->willReturn($combination);
        $helper->expects($this->once())
               ->method('hydrateStatusResponseToCombination')
               ->with($this->identicalTo($statusResponse), $this->identicalTo($combination));

        $result = $helper->createCombinationFromStatusResponse($statusResponse);

        $this->assertSame($combination, $result);
    }

    /**
     * Tests the createCombinationFromStatusResponse method.
     * @throws Exception
     * @covers ::createCombinationFromStatusResponse
     */
    public function testCreateCombinationFromStatusResponseWithExistingCombination(): void
    {
        $combinationIdString = 'af1da41d-55db-4075-9a34-1ee405a7c683';

        $combinationId = Uuid::fromString($combinationIdString);

        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);

        /* @var CombinationStatusResponse&MockObject $statusResponse */
        $statusResponse = $this->createMock(CombinationStatusResponse::class);
        $statusResponse->expects($this->once())
                       ->method('getId')
                       ->willReturn($combinationIdString);
        $statusResponse->expects($this->never())
                       ->method('getModNames');

        $this->combinationRepository->expects($this->once())
                                    ->method('getCombination')
                                    ->with($this->equalTo($combinationId))
                                    ->willReturn($combination);

        /* @var CombinationHelper&MockObject $helper */
        $helper = $this->getMockBuilder(CombinationHelper::class)
                       ->onlyMethods(['createCombination', 'hydrateStatusResponseToCombination'])
                       ->setConstructorArgs([$this->combinationRepository])
                       ->getMock();
        $helper->expects($this->never())
               ->method('createCombination');
        $helper->expects($this->once())
               ->method('hydrateStatusResponseToCombination')
               ->with($this->identicalTo($statusResponse), $this->identicalTo($combination));

        $result = $helper->createCombinationFromStatusResponse($statusResponse);

        $this->assertSame($combination, $result);
    }

    /**
     * Tests the createCombination method.
     * @throws ReflectionException
     * @covers ::createCombination
     */
    public function testCreateCombination(): void
    {
        $modNames = ['abc', 'def'];

        /* @var UuidInterface&MockObject $combinationId */
        $combinationId = $this->createMock(UuidInterface::class);

        $expectedResult = new Combination();
        $expectedResult->setId($combinationId)
                       ->setModNames($modNames);

        $helper = new CombinationHelper($this->combinationRepository);
        $result = $this->invokeMethod($helper, 'createCombination', $combinationId, $modNames);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the hydrateStatusResponseToCombination method.
     * @throws ReflectionException
     * @covers ::hydrateStatusResponseToCombination
     */
    public function testHydrateStatusResponseToCombinationWithStatusAvailable(): void
    {
        /* @var DateTime&MockObject $exportTime */
        $exportTime = $this->createMock(DateTime::class);

        $latestSuccessfulExportJob = new ExportJob();
        $latestSuccessfulExportJob->setExportTime($exportTime);

        $statusResponse = new CombinationStatusResponse();
        $statusResponse->setLatestSuccessfulExportJob($latestSuccessfulExportJob);

        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);
        $combination->expects($this->once())
                    ->method('setStatus')
                    ->with($this->identicalTo(CombinationStatus::AVAILABLE))
                    ->willReturnSelf();
        $combination->expects($this->once())
                    ->method('setExportTime')
                    ->with($this->identicalTo($exportTime))
                    ->willReturnSelf();
        $combination->expects($this->once())
                    ->method('setLastCheckTime')
                    ->with($this->isInstanceOf(DateTime::class))
                    ->willReturnSelf();

        $helper = new CombinationHelper($this->combinationRepository);
        $this->invokeMethod($helper, 'hydrateStatusResponseToCombination', $statusResponse, $combination);
    }

    /**
     * Tests the hydrateStatusResponseToCombination method.
     * @throws ReflectionException
     * @covers ::hydrateStatusResponseToCombination
     */
    public function testHydrateStatusResponseToCombinationWithStatusErrored(): void
    {
        $latestExportJob = new ExportJob();
        $latestExportJob->setStatus('error');

        $statusResponse = new CombinationStatusResponse();
        $statusResponse->setLatestExportJob($latestExportJob);

        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);
        $combination->expects($this->once())
                    ->method('setStatus')
                    ->with($this->identicalTo(CombinationStatus::ERRORED))
                    ->willReturnSelf();
        $combination->expects($this->never())
                    ->method('setExportTime');
        $combination->expects($this->once())
                    ->method('setLastCheckTime')
                    ->with($this->isInstanceOf(DateTime::class))
                    ->willReturnSelf();

        $helper = new CombinationHelper($this->combinationRepository);
        $this->invokeMethod($helper, 'hydrateStatusResponseToCombination', $statusResponse, $combination);
    }

    /**
     * Tests the hydrateStatusResponseToCombination method.
     * @throws ReflectionException
     * @covers ::hydrateStatusResponseToCombination
     */
    public function testHydrateStatusResponseToCombinationWithStatusPending(): void
    {
        $latestExportJob = new ExportJob();
        $latestExportJob->setStatus('queued');

        $statusResponse = new CombinationStatusResponse();
        $statusResponse->setLatestExportJob($latestExportJob);

        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);
        $combination->expects($this->once())
                    ->method('setStatus')
                    ->with($this->identicalTo(CombinationStatus::PENDING))
                    ->willReturnSelf();
        $combination->expects($this->never())
                    ->method('setExportTime');
        $combination->expects($this->once())
                    ->method('setLastCheckTime')
                    ->with($this->isInstanceOf(DateTime::class))
                    ->willReturnSelf();

        $helper = new CombinationHelper($this->combinationRepository);
        $this->invokeMethod($helper, 'hydrateStatusResponseToCombination', $statusResponse, $combination);
    }

    /**
     * Tests the hydrateStatusResponseToCombination method.
     * @throws ReflectionException
     * @covers ::hydrateStatusResponseToCombination
     */
    public function testHydrateStatusResponseToCombinationWithStatusUnknown(): void
    {
        $statusResponse = new CombinationStatusResponse();

        /* @var Combination&MockObject $combination */
        $combination = $this->createMock(Combination::class);
        $combination->expects($this->once())
                    ->method('setStatus')
                    ->with($this->identicalTo(CombinationStatus::UNKNOWN))
                    ->willReturnSelf();
        $combination->expects($this->never())
                    ->method('setExportTime');
        $combination->expects($this->once())
                    ->method('setLastCheckTime')
                    ->with($this->isInstanceOf(DateTime::class))
                    ->willReturnSelf();

        $helper = new CombinationHelper($this->combinationRepository);
        $this->invokeMethod($helper, 'hydrateStatusResponseToCombination', $statusResponse, $combination);
    }
}
