<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Command;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\CommandName;
use FactorioItemBrowser\PortalApi\Server\Repository\SettingRepository;
use FactorioItemBrowser\PortalApi\Server\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The command for clearing old sessions.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class CleanSessionsCommand extends Command
{
    /**
     * The setting repository.
     * @var SettingRepository
     */
    protected $settingRepository;

    /**
     * The user repository.
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * The life time of the sessions.
     * @var string
     */
    protected $sessionLifeTime;

    /**
     * The life time of the temporary settings.
     * @var string
     */
    protected $temporarySettingLifeTime;

    /**
     * Initializes the command.
     * @param SettingRepository $settingRepository
     * @param UserRepository $userRepository
     * @param string $sessionLifeTime
     * @param string $temporarySettingLifeTime
     */
    public function __construct(
        SettingRepository $settingRepository,
        UserRepository $userRepository,
        string $sessionLifeTime,
        string $temporarySettingLifeTime
    ) {
        parent::__construct();

        $this->settingRepository = $settingRepository;
        $this->userRepository = $userRepository;
        $this->sessionLifeTime = $sessionLifeTime;
        $this->temporarySettingLifeTime = $temporarySettingLifeTime;
    }

    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName(CommandName::CLEAN_SESSIONS);
        $this->setDescription('Cleans up old sessions from the database.');
    }

    /**
     * Executes the command.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws DriverException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->userRepository->cleanupOldSessions($this->calculateTimeCut($this->sessionLifeTime));
        $this->settingRepository->cleanupTemporarySettings($this->calculateTimeCut($this->temporarySettingLifeTime));

        return 0;
    }

    /**
     * Calculates the time cut for the sessions.
     * @param string $lifeTime
     * @return DateTimeInterface
     * @throws Exception
     */
    protected function calculateTimeCut(string $lifeTime): DateTimeInterface
    {
        $timeCut = new DateTime();
        $interval = $timeCut->diff(new DateTime($lifeTime));
        $timeCut->sub($interval);
        return $timeCut;
    }
}
