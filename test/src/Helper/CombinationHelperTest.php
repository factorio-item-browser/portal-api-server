<?php

declare(strict_types=1);

namespace FactorioItemBrowserTest\PortalApi\Server\Helper;

use DateTime;
use FactorioItemBrowser\CombinationApi\Client\ClientInterface;
use FactorioItemBrowser\CombinationApi\Client\Constant\JobStatus;
use FactorioItemBrowser\CombinationApi\Client\Constant\ListOrder;
use FactorioItemBrowser\CombinationApi\Client\Exception\ClientException;
use FactorioItemBrowser\CombinationApi\Client\Request\Combination\StatusRequest;
use FactorioItemBrowser\CombinationApi\Client\Request\Job\CreateRequest;
use FactorioItemBrowser\CombinationApi\Client\Request\Job\ListRequest;
use FactorioItemBrowser\CombinationApi\Client\Response\Combination\StatusResponse;
use FactorioItemBrowser\CombinationApi\Client\Response\Job\DetailsResponse;
use FactorioItemBrowser\CombinationApi\Client\Response\Job\ListResponse;
use FactorioItemBrowser\CombinationApi\Client\Transfer\Job;
use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedCombinationApiException;
use FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper;
use FactorioItemBrowser\PortalApi\Server\Repository\CombinationRepository;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * The PHPUnit test of the CombinationHelper class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @covers \FactorioItemBrowser\PortalApi\Server\Helper\CombinationHelper
 */
class CombinationHelperTest extends TestCase
{
    /** @var ClientInterface&MockObject */
    private ClientInterface $combinationApiClient;
    /** @var CombinationRepository&MockObject */
    private CombinationRepository $combinationRepository;

    protected function setUp(): void
    {
        $this->combinationApiClient = $this->createMock(ClientInterface::class);
        $this->combinationRepository = $this->createMock(CombinationRepository::class);
    }

    /**
     * @param array<string> $mockedMethods
     * @return CombinationHelper&MockObject
     */
    private function createInstance(array $mockedMethods = []): CombinationHelper
    {
        return $this->getMockBuilder(CombinationHelper::class)
                    ->disableProxyingToOriginalMethods()
                    ->onlyMethods($mockedMethods)
                    ->setConstructorArgs([
                        $this->combinationApiClient,
                        $this->combinationRepository,
                    ])
                    ->getMock();
    }

    /**
     * @throws FailedCombinationApiException
     */
    public function testCreateForModNames(): void
    {
        $modNames = ['abc', 'def'];

        $expectedApiRequest = new StatusRequest();
        $expectedApiRequest->modNames = $modNames;
        $statusResponse = new StatusResponse();
        $statusResponse->id = '78c85405-bfc0-4600-acd9-b992c871812b';
        $statusResponse->isDataAvailable = true;

        $combination = $this->createMock(Combination::class);

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedApiRequest))
                                   ->willReturn(new FulfilledPromise($statusResponse));

        $this->combinationRepository->expects($this->once())
                                    ->method('getCombination')
                                    ->with($this->equalTo(Uuid::fromString('78c85405-bfc0-4600-acd9-b992c871812b')))
                                    ->willReturn($combination);

        $this->combinationRepository->expects($this->once())
                                    ->method('persist')
                                    ->with($this->identicalTo($combination));

        $instance = $this->createInstance();
        $result = $instance->createForModNames($modNames);

        $this->assertSame($combination, $result);
    }

    /**
     * @throws FailedCombinationApiException
     */
    public function testCreateForModNamesWithoutCombination(): void
    {
        $modNames = ['abc', 'def'];

        $expectedApiRequest = new StatusRequest();
        $expectedApiRequest->modNames = $modNames;
        $statusResponse = new StatusResponse();
        $statusResponse->id = '78c85405-bfc0-4600-acd9-b992c871812b';
        $statusResponse->modNames = $modNames;
        $statusResponse->isDataAvailable = false;

        $expectedApiListRequest = new ListRequest();
        $expectedApiListRequest->combinationId = '78c85405-bfc0-4600-acd9-b992c871812b';
        $expectedApiListRequest->order = ListOrder::LATEST;
        $expectedApiListRequest->limit = 1;
        $listResponse = new ListResponse();

        $expectedCombination = new Combination();
        $expectedCombination->setId(Uuid::fromString('78c85405-bfc0-4600-acd9-b992c871812b'))
                            ->setModNames($modNames)
                            ->setStatus(CombinationStatus::UNKNOWN);

        $this->combinationApiClient->expects($this->exactly(2))
                                   ->method('sendRequest')
                                   ->withConsecutive(
                                       [$this->equalTo($expectedApiRequest)],
                                       [$this->equalTo($expectedApiListRequest)],
                                   )
                                   ->willReturnOnConsecutiveCalls(
                                       new FulfilledPromise($statusResponse),
                                       new FulfilledPromise($listResponse),
                                   );

        $this->combinationRepository->expects($this->once())
                                    ->method('getCombination')
                                    ->with($this->equalTo(Uuid::fromString('78c85405-bfc0-4600-acd9-b992c871812b')))
                                    ->willReturn(null);

        $this->combinationRepository->expects($this->never())
                                    ->method('persist');

        $instance = $this->createInstance();
        $result = $instance->createForModNames($modNames);

        $expectedCombination->setLastCheckTime($result->getLastCheckTime());
        $this->assertEquals($expectedCombination, $result);
    }

    public function testCreateForModNamesWithApiException(): void
    {
        $modNames = ['abc', 'def'];

        $expectedApiRequest = new StatusRequest();
        $expectedApiRequest->modNames = $modNames;

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedApiRequest))
                                   ->willThrowException($this->createMock(ClientException::class));

        $this->combinationRepository->expects($this->never())
                                    ->method('getCombination');

        $this->combinationRepository->expects($this->never())
                                    ->method('persist');

        $this->expectException(FailedCombinationApiException::class);

        $instance = $this->createInstance();
        $instance->createForModNames($modNames);
    }

    /**
     * @throws FailedCombinationApiException
     */
    public function testUpdateStatus(): void
    {
        $exportTime = new DateTime('2038-01-19 03:14:07');

        $expectedApiRequest = new StatusRequest();
        $expectedApiRequest->combinationId = '78c85405-bfc0-4600-acd9-b992c871812b';
        $statusResponse = new StatusResponse();
        $statusResponse->isDataAvailable = true;
        $statusResponse->exportTime = $exportTime;

        $combination = $this->createMock(Combination::class);
        $combination->expects($this->any())
                    ->method('getId')
                    ->willReturn(Uuid::fromString('78c85405-bfc0-4600-acd9-b992c871812b'));
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

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedApiRequest))
                                   ->willReturn(new FulfilledPromise($statusResponse));

        $this->combinationRepository->expects($this->once())
                                    ->method('persist')
                                    ->with($this->identicalTo($combination));

        $instance = $this->createInstance();
        $instance->updateStatus($combination);
    }

    public function testUpdateStatusWithApiException(): void
    {
        $expectedApiRequest = new StatusRequest();
        $expectedApiRequest->combinationId = '78c85405-bfc0-4600-acd9-b992c871812b';

        $combination = $this->createMock(Combination::class);
        $combination->expects($this->any())
                    ->method('getId')
                    ->willReturn(Uuid::fromString('78c85405-bfc0-4600-acd9-b992c871812b'));

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedApiRequest))
                                   ->willThrowException($this->createMock(ClientException::class));

        $this->combinationRepository->expects($this->never())
                                    ->method('persist');

        $this->expectException(FailedCombinationApiException::class);

        $instance = $this->createInstance();
        $instance->updateStatus($combination);
    }

    /**
     * @throws FailedCombinationApiException
     */
    public function testUpdateStatusWithJobRequest(): void
    {
        $expectedApiRequest = new StatusRequest();
        $expectedApiRequest->combinationId = '78c85405-bfc0-4600-acd9-b992c871812b';
        $statusResponse = new StatusResponse();
        $statusResponse->isDataAvailable = false;

        $expectedApiListRequest = new ListRequest();
        $expectedApiListRequest->combinationId = '78c85405-bfc0-4600-acd9-b992c871812b';
        $expectedApiListRequest->order = ListOrder::LATEST;
        $expectedApiListRequest->limit = 1;
        $job = new Job();
        $job->status = JobStatus::ERROR;
        $listResponse = new ListResponse();
        $listResponse->jobs = [$job];

        $combination = $this->createMock(Combination::class);
        $combination->expects($this->any())
                    ->method('getId')
                    ->willReturn(Uuid::fromString('78c85405-bfc0-4600-acd9-b992c871812b'));
        $combination->expects($this->once())
                    ->method('setStatus')
                    ->with($this->identicalTo(CombinationStatus::ERRORED))
                    ->willReturnSelf();
        $combination->expects($this->once())
                    ->method('setLastCheckTime')
                    ->with($this->isInstanceOf(DateTime::class))
                    ->willReturnSelf();

        $this->combinationApiClient->expects($this->exactly(2))
                                   ->method('sendRequest')
                                   ->withConsecutive(
                                       [$this->equalTo($expectedApiRequest)],
                                       [$this->equalTo($expectedApiListRequest)],
                                   )
                                   ->willReturnOnConsecutiveCalls(
                                       new FulfilledPromise($statusResponse),
                                       new FulfilledPromise($listResponse),
                                   );

        $this->combinationRepository->expects($this->once())
                                    ->method('persist')
                                    ->with($this->identicalTo($combination));

        $instance = $this->createInstance();
        $instance->updateStatus($combination);
    }

    public function testUpdateStatusWithFailedJobRequest(): void
    {
        $expectedApiRequest = new StatusRequest();
        $expectedApiRequest->combinationId = '78c85405-bfc0-4600-acd9-b992c871812b';
        $statusResponse = new StatusResponse();
        $statusResponse->isDataAvailable = false;

        $expectedApiListRequest = new ListRequest();
        $expectedApiListRequest->combinationId = '78c85405-bfc0-4600-acd9-b992c871812b';
        $expectedApiListRequest->order = ListOrder::LATEST;
        $expectedApiListRequest->limit = 1;

        $combination = $this->createMock(Combination::class);
        $combination->expects($this->any())
                    ->method('getId')
                    ->willReturn(Uuid::fromString('78c85405-bfc0-4600-acd9-b992c871812b'));

        $this->combinationApiClient->expects($this->exactly(2))
                                   ->method('sendRequest')
                                   ->withConsecutive(
                                       [$this->equalTo($expectedApiRequest)],
                                       [$this->equalTo($expectedApiListRequest)],
                                   )
                                   ->willReturnOnConsecutiveCalls(
                                       new FulfilledPromise($statusResponse),
                                       $this->throwException($this->createMock(ClientException::class)),
                                   );

        $this->combinationRepository->expects($this->never())
                                    ->method('persist');

        $this->expectException(FailedCombinationApiException::class);

        $instance = $this->createInstance();
        $instance->updateStatus($combination);
    }

    /**
     * @throws FailedCombinationApiException
     */
    public function testTriggerExport(): void
    {
        $combinationId = Uuid::fromString('78c85405-bfc0-4600-acd9-b992c871812b');

        $combination = new Combination();
        $combination->setId($combinationId);

        $expectedApiRequest = new CreateRequest();
        $expectedApiRequest->combinationId = '78c85405-bfc0-4600-acd9-b992c871812b';

        $apiResponse = new DetailsResponse();
        $apiResponse->status = JobStatus::QUEUED;

        $expectedCombination = new Combination();
        $expectedCombination->setId($combinationId)
                            ->setStatus(CombinationStatus::PENDING);

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedApiRequest))
                                   ->willReturn(new FulfilledPromise($apiResponse));

        $this->combinationRepository->expects($this->once())
                                    ->method('persist')
                                    ->with($this->equalTo($expectedCombination));

        $instance = $this->createInstance();
        $instance->triggerExport($combination);

        $this->assertEquals($expectedCombination, $combination);
    }

    public function testTriggerExportWithApiException(): void
    {
        $combinationId = Uuid::fromString('78c85405-bfc0-4600-acd9-b992c871812b');

        $combination = new Combination();
        $combination->setId($combinationId);

        $expectedApiRequest = new CreateRequest();
        $expectedApiRequest->combinationId = '78c85405-bfc0-4600-acd9-b992c871812b';

        $this->combinationApiClient->expects($this->once())
                                   ->method('sendRequest')
                                   ->with($this->equalTo($expectedApiRequest))
                                   ->willThrowException($this->createMock(ClientException::class));

        $this->combinationRepository->expects($this->never())
                                    ->method('persist');

        $this->expectException(FailedCombinationApiException::class);

        $instance = $this->createInstance();
        $instance->triggerExport($combination);
    }
}
