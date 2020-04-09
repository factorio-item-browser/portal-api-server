<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Helper;

use DateTime;
use Exception;
use FactorioItemBrowser\Api\Client\Constant\ExportJobStatus;
use FactorioItemBrowser\Api\Client\Response\Combination\CombinationStatusResponse;
use FactorioItemBrowser\PortalApi\Server\Constant\CombinationStatus;
use FactorioItemBrowser\PortalApi\Server\Entity\Combination;
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
    /**
     * The combination repository.
     * @var CombinationRepository
     */
    protected $combinationRepository;

    /**
     * Initializes the helper.
     * @param CombinationRepository $combinationRepository
     */
    public function __construct(CombinationRepository $combinationRepository)
    {
        $this->combinationRepository = $combinationRepository;
    }

    /**
     * Creates a combination from the specified status response.
     * @param CombinationStatusResponse $statusResponse
     * @return Combination
     * @throws Exception
     */
    public function createCombinationFromStatusResponse(CombinationStatusResponse $statusResponse): Combination
    {
        $combinationId = Uuid::fromString($statusResponse->getId());
        $combination = $this->combinationRepository->getCombination($combinationId);
        if ($combination === null) {
            $combination = $this->createCombination($combinationId, $statusResponse->getModNames());
        }

        $this->hydrateStatusResponseToCombination($statusResponse, $combination);
        return $combination;
    }

    /**
     * Creates a new combination instance.
     * @param UuidInterface $combinationId
     * @param array<string> $modNames
     * @return Combination
     */
    protected function createCombination(UuidInterface $combinationId, array $modNames): Combination
    {
        $combination = new Combination();
        $combination->setId($combinationId)
                    ->setModNames($modNames);
        return $combination;
    }

    /**
     * Hydrates the values from the status response to the combination.
     * @param CombinationStatusResponse $statusResponse
     * @param Combination $combination
     * @throws Exception
     */
    public function hydrateStatusResponseToCombination(
        CombinationStatusResponse $statusResponse,
        Combination $combination
    ): void {
        if ($statusResponse->getLatestSuccessfulExportJob() !== null) {
            $combination->setStatus(CombinationStatus::AVAILABLE)
                        ->setExportTime($statusResponse->getLatestSuccessfulExportJob()->getExportTime());
        } elseif ($statusResponse->getLatestExportJob() !== null) {
            if ($statusResponse->getLatestExportJob()->getStatus() === ExportJobStatus::ERROR) {
                $combination->setStatus(CombinationStatus::ERRORED);
            } else {
                $combination->setStatus(CombinationStatus::PENDING);
            }
        } else {
            $combination->setStatus(CombinationStatus::UNKNOWN);
        }

        $combination->setLastCheckTime(new DateTime());
    }

    /**
     * Persists the combination to the database, creating its dataset if it was newly created.
     * @param Combination $combination
     */
    public function persist(Combination $combination): void
    {
        $this->combinationRepository->persist($combination);
    }
}
