<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a restaurant application user'
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('role', InputArgument::OPTIONAL, 'ROLE_MANAGER, ROLE_WAITER or ROLE_KITCHEN', 'ROLE_WAITER');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = strtolower(trim((string) $input->getArgument('email')));
        $role = strtoupper(trim((string) $input->getArgument('role')));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Adresse email invalide.');
            return Command::INVALID;
        }

        $allowedRoles = ['ROLE_MANAGER', 'ROLE_WAITER', 'ROLE_KITCHEN'];

        if (!in_array($role, $allowedRoles, true)) {
            $io->error('Rôle invalide. Choisissez ROLE_MANAGER, ROLE_WAITER ou ROLE_KITCHEN.');
            return Command::INVALID;
        }

        if ($this->userRepository->findOneBy(['email' => $email])) {
            $io->error('Un utilisateur avec cet email existe déjà.');
            return Command::FAILURE;
        }

        $password = $io->askHidden('Mot de passe (minimum 8 caractères)');
        $confirmation = $io->askHidden('Confirmer le mot de passe');

        if (!is_string($password) || strlen($password) < 8) {
            $io->error('Le mot de passe doit contenir au moins 8 caractères.');
            return Command::INVALID;
        }

        if ($password !== $confirmation) {
            $io->error('Les mots de passe ne correspondent pas.');
            return Command::INVALID;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles([$role]);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Utilisateur %s créé avec le rôle %s.', $email, $role));

        return Command::SUCCESS;
    }
}