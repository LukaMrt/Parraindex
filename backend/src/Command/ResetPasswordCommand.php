<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\AuthService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reset-password',
    description: 'Réinitialise le mot de passe d\'un utilisateur à "password"',
)]
final class ResetPasswordCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AuthService $authService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, "ID de l'utilisateur");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io    = new SymfonyStyle($input, $output);
        $rawId = $input->getArgument('id');

        if (!is_numeric($rawId)) {
            $io->error('L\'identifiant doit être un entier.');
            return Command::FAILURE;
        }

        $id = (int) $rawId;

        $user = $this->userRepository->find($id);

        if ($user === null) {
            $io->error(sprintf('Utilisateur #%d introuvable.', $id));
            return Command::FAILURE;
        }

        $this->authService->resetPassword($user, 'password');

        $io->success(sprintf('Mot de passe de %s réinitialisé à "password".', $user->getEmail()));

        return Command::SUCCESS;
    }
}
