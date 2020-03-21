<?php

declare(strict_types=1);

namespace FactorioItemBrowser\PortalApi\Server\Command;

use DateTime;
use DateTimeInterface;
use Exception;
use FactorioItemBrowser\PortalApi\Server\Constant\CommandName;
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
     * Initializes the command.
     * @param UserRepository $userRepository
     * @param string $sessionLifeTime
     */
    public function __construct(UserRepository $userRepository, string $sessionLifeTime)
    {
        parent::__construct();

        $this->userRepository = $userRepository;
        $this->sessionLifeTime = $sessionLifeTime;
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
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $timeCut = $this->calculateTimeCut();
        $this->userRepository->cleanupOldSessions($timeCut);
        return 0;
    }

    /**
     * Calculates the time cut for the sessions.
     * @return DateTimeInterface
     * @throws Exception
     */
    protected function calculateTimeCut(): DateTimeInterface
    {
        $timeCut = new DateTime();
        $interval = $timeCut->diff(new DateTime($this->sessionLifeTime));
        $timeCut->sub($interval);
        return $timeCut;
    }
}
