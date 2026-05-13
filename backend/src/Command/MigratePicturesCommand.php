<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\PersonRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-pictures',
    description: 'Migre les images de profil de public/uploads/pictures/ vers public/uploads/avatars/ avec nommage SHA-256',
)]
final class MigratePicturesCommand extends Command
{
    public function __construct(
        private readonly PersonRepository $personRepository,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io     = new SymfonyStyle($input, $output);
        $oldDir = $this->projectDir . '/public/uploads/pictures/';
        $newDir = $this->projectDir . '/public/uploads/avatars/';

        if (!is_dir($newDir) && !mkdir($newDir, 0755, true) && !is_dir($newDir)) {
            $io->error('Impossible de créer le dossier ' . $newDir);
            return Command::FAILURE;
        }

        $persons  = $this->personRepository->findAll();
        $migrated = 0;
        $skipped  = 0;
        $missing  = 0;

        foreach ($persons as $person) {
            $oldName = $person->getPicture();

            if ($oldName === null) {
                continue;
            }

            // Ancien placeholder sans image → NULL
            if ($oldName === 'no-picture.svg') {
                $person->setPicture(null);
                $this->personRepository->update($person);
                $io->writeln(sprintf('  <comment>∅</comment> %s → NULL (no-picture.svg)', $person->getFullName()));
                ++$migrated;
                continue;
            }

            // Déjà migré (hash SHA-256 de 40 chars)
            if (preg_match('/^[0-9a-f]{40}\.[a-z]+$/', $oldName)) {
                ++$skipped;
                continue;
            }

            $oldPath = $oldDir . $oldName;

            if (!file_exists($oldPath)) {
                $io->warning('Fichier introuvable, ignoré : ' . $oldName);
                ++$missing;
                continue;
            }

            $hash    = hash_file('sha256', $oldPath);
            $ext     = pathinfo($oldName, PATHINFO_EXTENSION);
            $newName = substr((string) $hash, 0, 40) . '.' . $ext;
            $newPath = $newDir . $newName;

            if (file_exists($newPath)) {
                $io->writeln(sprintf('  <info>⟳</info> %s → %s (déjà présent, BDD mise à jour)', $oldName, $newName));
            } else {
                if (!copy($oldPath, $newPath)) {
                    $io->error(sprintf('Échec de la copie : %s → %s', $oldName, $newName));
                    return Command::FAILURE;
                }

                $io->writeln(sprintf('  <info>✓</info> %s → %s', $oldName, $newName));
            }

            $person->setPicture($newName);
            $this->personRepository->update($person);
            ++$migrated;
        }

        $io->success(sprintf('Migration terminée : %d migrée(s), %d déjà à jour, %d introuvable(s).', $migrated, $skipped, $missing));

        return Command::SUCCESS;
    }
}
