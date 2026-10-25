<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Command;

use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Service\PlayerStateUpdaterService;
use FrankProjects\UltimateWarfare\Service\WaterRegionUpdaterService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Worker that completes queued construction and research of all players within seconds of their completion, and
 * grows their population at every population tick. It also releases water regions that lost their last units.
 * Runs continuously under a process manager (config/systemd/ultimatewarfare-worker.service); --once does a single
 * pass. Requests don't depend on it for correctness: each state-changing request updates its own player first.
 */
#[AsCommand(name: 'game:queue:work')]
class QueueWorkerCommand extends Command implements SignalableCommandInterface
{
    private PlayerStateUpdaterService $playerStateUpdaterService;
    private WaterRegionUpdaterService $waterRegionUpdaterService;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private bool $shouldStop = false;

    public function __construct(
        PlayerStateUpdaterService $playerStateUpdaterService,
        WaterRegionUpdaterService $waterRegionUpdaterService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->playerStateUpdaterService = $playerStateUpdaterService;
        $this->waterRegionUpdaterService = $waterRegionUpdaterService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Process completed construction and research of all players');
        $this->setHelp(
            'Keeps running and processes completed construction and research every --interval seconds, ' .
            'until --time-limit is reached or it receives SIGTERM/SIGINT. Run it under a process manager ' .
            '(systemd/supervisor) that restarts it, see config/systemd/ultimatewarfare-worker.service' . PHP_EOL .
            'Use --once for a single pass.'
        );
        $this->addOption('once', null, InputOption::VALUE_NONE, 'Process once and exit');
        $this->addOption('interval', null, InputOption::VALUE_REQUIRED, 'Seconds between passes', '2');
        $this->addOption(
            'time-limit',
            null,
            InputOption::VALUE_REQUIRED,
            'Exit after this many seconds, so the process manager starts a fresh process',
            '3600'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('once') === true) {
            $this->processPass();
            $output->writeln('Done!');

            return Command::SUCCESS;
        }

        /** @var string $intervalOption */
        $intervalOption = $input->getOption('interval');
        /** @var string $timeLimitOption */
        $timeLimitOption = $input->getOption('time-limit');
        $interval = max(1, intval($intervalOption));
        $deadline = time() + max(1, intval($timeLimitOption));

        $output->writeln("Processing the queue every {$interval} second(s)");

        while (!$this->shouldStop && time() < $deadline) {
            try {
                $this->processPass();
            } catch (\Throwable $e) {
                // A failed flush closes the EntityManager; exit and let the process manager start a fresh process
                $this->logger->error('Queue worker pass failed: ' . $e->getMessage(), ['exception' => $e]);
                $output->writeln('<error>' . $e->getMessage() . '</error>');

                return Command::FAILURE;
            }

            // A long-running process must not keep (stale) entities between passes
            $this->entityManager->clear();

            $this->sleepUnlessStopped($interval);
        }

        $output->writeln('Stopped');

        return Command::SUCCESS;
    }

    private function processPass(): void
    {
        $this->playerStateUpdaterService->updateDuePlayers(time());
        $this->waterRegionUpdaterService->releaseUnoccupiedRegions();
    }

    /**
     * Sleeps in steps of one second, so a stop signal (handled while sleeping) is acted on promptly
     */
    private function sleepUnlessStopped(int $seconds): void
    {
        for ($slept = 0; $slept < $seconds; $slept++) {
            if ($this->shouldStop) {
                return;
            }
            sleep(1);
        }
    }

    /**
     * @return list<int>
     */
    public function getSubscribedSignals(): array
    {
        // Without the pcntl extension the process is simply killed on stop
        if (!\extension_loaded('pcntl')) {
            return [];
        }

        return [\SIGTERM, \SIGINT];
    }

    public function handleSignal(int $signal, int|false $previousExitCode = 0): int|false
    {
        // Finish the current pass (and its transactions) instead of being killed halfway
        $this->shouldStop = true;

        return false;
    }
}
