<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\PersonRepository;
use App\Service\AvatarCacheService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'app:deploy-reset',
    description: 'Post-déploiement : vide les sessions et régénère le cache des avatars en WebP.',
)]
final class DeployResetCommand extends Command
{
    public function __construct(
        private readonly PersonRepository $personRepository,
        private readonly AvatarCacheService $avatarCacheService,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // ── 1. Vider les sessions ─────────────────────────────────────────────
        $io->section('Invalidation des sessions');

        $sessionDir = $this->projectDir . '/var/sessions';
        $cleared    = 0;

        if (is_dir($sessionDir)) {
            $finder = new Finder();
            $finder->files()->in($sessionDir);

            foreach ($finder as $file) {
                unlink($file->getRealPath());
                ++$cleared;
            }
        }

        $io->writeln(sprintf('  <info>✓</info> %d session(s) supprimée(s)', $cleared));

        // ── 2. Régénérer le cache des avatars ────────────────────────────────
        $io->section('Régénération du cache avatars (WebP)');

        $persons = $this->personRepository->findAll();
        $warmed  = 0;
        $skipped = 0;

        foreach ($persons as $person) {
            $picture = $person->getPicture();

            if ($picture === null) {
                ++$skipped;
                continue;
            }

            try {
                $this->avatarCacheService->warmUp($picture);
                $io->writeln(sprintf('  <info>✓</info> %s', $person->getFullName()));
                ++$warmed;
            } catch (\Throwable $e) {
                $io->warning(sprintf('%s — %s', $person->getFullName(), $e->getMessage()));
            }
        }

        $io->success(sprintf(
            '%d avatar(s) régénéré(s), %d sans image, %d session(s) invalidée(s).',
            $warmed,
            $skipped,
            $cleared,
        ));

        return Command::SUCCESS;
    }
}
