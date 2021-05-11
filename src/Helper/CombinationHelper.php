<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Helper;

use DateTime;
use FactorioItemBrowser\CombinationApi\Client\ClientInterface;
use FactorioItemBrowser\CombinationApi\Client\Constant\JobStatus;
use FactorioItemBrowser\CombinationApi\Client\Constant\ListOrder;
use FactorioItemBrowser\CombinationApi\Client\Exception\ClientException;
use FactorioItemBrowser\CombinationApi\Client\Request\Combination\StatusRequest;
use FactorioItemBrowser\CombinationApi\Client\Request\Combination\ValidateRequest;
use FactorioItemBrowser\CombinationApi\Client\Request\Job\CreateRequest;
use FactorioItemBrowser\CombinationApi\Client\Request\Job\ListRequest;
use FactorioItemBrowser\CombinationApi\Client\Response\Combination\StatusResponse;
use FactorioItemBrowser\CombinationApi\Client\Response\Combination\ValidateResponse;
use FactorioItemBrowser\CombinationApi\Client\Response\Job\DetailsResponse;
use FactorioItemBrowser\CombinationApi\Client\Response\Job\ListResponse;
use FactorioItemBrowser\CombinationApi\Client\Transfer\Job;
use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
use FactorioItemBrowser\PortalApi\Server\Exception\FailedCombinationApiException;
use FactorioItemBrowser\PortalApi\Server\Repository\CombinationRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * The helper for managing the combinations.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CombinationHelper
{
    private ClientInterface $combinationApiClient;
    private CombinationRepository $combinationRepository;

    public function __construct(ClientInterface $combinationApiClient, CombinationRepository $combinationRepository)
    {
        $this->combinationApiClient = $combinationApiClient;
        $this->combinationRepository = $combinationRepository;
    }

    /**
     * Creates a combination representing the specified mod names, requesting its latest status.
     * @param array<string> $modNames
     * @return Combination
     * @throws FailedCombinationApiException
     */
    public function createForModNames(array $modNames): Combination
    {
        $request = new StatusRequest();
        $request->modNames = $modNames;

        try {
            /** @var StatusResponse $response */
            $response = $this->combinationApiClient->sendRequest($request)->wait();
        } catch (ClientException $e) {
            throw new FailedCombinationApiException($e);
        }

        $combinationId = Uuid::fromString($response->id);
        $combination = $this->combinationRepository->getCombination($combinationId);
        if ($combination === null) {
            $combination = new Combination();
            $combination->setId($combinationId)
                        ->setModNames($response->modNames)
                        ->setStatus(CombinationStatus::UNKNOWN);
        }

        $this->applyStatusResponse($combination, $response);
        $this->persist($combination);
        return $combination;
    }

    /**
     * Creates the combination for the specified id, requesting its details from the Combination API if necessary.
     * @param UuidInterface $combinationId
     * @return Combination
     * @throws FailedCombinationApiException
     */
    public function createForCombinationId(UuidInterface $combinationId): Combination
    {
        $combination = $this->combinationRepository->getCombination($combinationId);
        if ($combination !== null) {
            return $combination;
        }

        $request = new StatusRequest();
        $request->combinationId = $combinationId->toString();

        try {
            /** @var StatusResponse $response */
            $response = $this->combinationApiClient->sendRequest($request)->wait();
        } catch (ClientException $e) {
            throw new FailedCombinationApiException($e);
        }

        $combination = new Combination();
        $combination->setId($combinationId)
                    ->setModNames($response->modNames)
                    ->setStatus(CombinationStatus::UNKNOWN);

        $this->applyStatusResponse($combination, $response);
        $this->persist($combination);
        return $combination;
    }

    /**
     * Updates the status of the specified combination.
     * @param Combination $combination
     * @throws FailedCombinationApiException
     */
    public function updateStatus(Combination $combination): void
    {
        $statusRequest = new StatusRequest();
        $statusRequest->combinationId = $combination->getId()->toString();
        try {
            /** @var StatusResponse $statusResponse */
            $statusResponse = $this->combinationApiClient->sendRequest($statusRequest)->wait();
        } catch (ClientException $e) {
            throw new FailedCombinationApiException($e);
        }

        $this->applyStatusResponse($combination, $statusResponse);
        $this->persist($combination);
    }

    /**
     * Triggers a new export for the specified combination.
     * @param Combination $combination
     * @throws FailedCombinationApiException
     */
    public function triggerExport(Combination $combination): void
    {
        $createRequest = new CreateRequest();
        $createRequest->combinationId = $combination->getId()->toString();

        try {
            /** @var DetailsResponse $detailsResponse */
            $detailsResponse = $this->combinationApiClient->sendRequest($createRequest)->wait();
        } catch (ClientException $e) {
            throw new FailedCombinationApiException($e);
        }

        $this->applyJob($combination, $detailsResponse);
        $this->persist($combination);
    }

    /**
     * Applies the status response to the combination, requesting the latest job if further details are required.
     * @param Combination $combination
     * @param StatusResponse $statusResponse
     * @throws FailedCombinationApiException
     */
    private function applyStatusResponse(Combination $combination, StatusResponse $statusResponse): void
    {
        if ($statusResponse->isDataAvailable) {
            $combination->setStatus(CombinationStatus::AVAILABLE)
                        ->setExportTime($statusResponse->exportTime);
        } else {
            $listRequest = new ListRequest();
            $listRequest->combinationId = $combination->getId()->toString();
            $listRequest->order = ListOrder::LATEST;
            $listRequest->limit = 1;

            try {
                /** @var ListResponse $listResponse */
                $listResponse = $this->combinationApiClient->sendRequest($listRequest)->wait();
            } catch (ClientException $e) {
                throw new FailedCombinationApiException($e);
            }

            $job = $listResponse->jobs[0] ?? null;
            if ($job !== null) {
                $this->applyJob($combination, $job);
            }
        }
        $combination->setLastCheckTime(new DateTime());
    }

    /**
     * Applies the job details to the combination.
     * @param Combination $combination
     * @param Job $job
     */
    private function applyJob(Combination $combination, Job $job): void
    {
        if ($job->status === JobStatus::ERROR) {
            $combination->setStatus(CombinationStatus::ERRORED);
        } else {
            $combination->setStatus(CombinationStatus::PENDING);
        }
    }

    private function persist(Combination $combination): void
    {
        if ($combination->getStatus() !== CombinationStatus::UNKNOWN) {
            $this->combinationRepository->persist($combination);
        }
    }

    /**
     * Validates the combination.
     * @param Combination $combination
     * @param string $factorioVersion
     * @return ValidateResponse
     * @throws FailedCombinationApiException
     */
    public function validate(Combination $combination, string $factorioVersion): ValidateResponse
    {
        $request = new ValidateRequest();
        $request->combinationId = $combination->getId()->toString();
        $request->factorioVersion = $factorioVersion;

        try {
            /** @var ValidateResponse $response */
            $response = $this->combinationApiClient->sendRequest($request)->wait();
            return $response;
        } catch (ClientException $e) {
            throw new FailedCombinationApiException($e);
        }
    }
}
