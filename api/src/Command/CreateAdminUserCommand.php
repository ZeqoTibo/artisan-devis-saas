<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user:create-admin',
    description: 'Crée un utilisateur ROLE_ADMIN (accès CRUD /api/users). À utiliser une première fois hors API.',
)]
final class CreateAdminUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = (string) $input->getArgument('email');
        $plainPassword = (string) $input->getArgument('password');

        $repo = $this->entityManager->getRepository(User::class);
        if (null !== $repo->findOneBy(['email' => $email])) {
            $io->error(sprintf('Un utilisateur avec l’email %s existe déjà.', $email));

            return Command::FAILURE;
        }

        $user = (new User())
            ->setEmail($email)
            ->setRole('ROLE_ADMIN')
            ->setName('Administrateur');

        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        $now = new \DateTimeImmutable();
        $user->setCreatedAt($now);
        $user->setUpdatedAt($now);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Admin créé : %s', $email));

        return Command::SUCCESS;
    }
}
