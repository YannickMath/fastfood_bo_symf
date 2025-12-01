<?php

namespace App\Command;

use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:promote-admin',
    description: 'Promote a user to ROLE_ADMIN by email',
)]
class PromoteUserToAdminCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UsersRepository $usersRepository;

    public function __construct(EntityManagerInterface $entityManager, UsersRepository $usersRepository)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->usersRepository = $usersRepository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user to promote');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $user = $this->usersRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $io->error(sprintf('User with email "%s" not found.', $email));
            return Command::FAILURE;
        }

        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            $io->warning(sprintf('User "%s" (%s) is already an admin.', $user->getUsername(), $email));
            return Command::SUCCESS;
        }

        $roles[] = 'ROLE_ADMIN';
        $user->setRoles($roles);

        $this->entityManager->flush();

        $io->success(sprintf('User "%s" (%s) has been promoted to ROLE_ADMIN.', $user->getUsername(), $email));

        return Command::SUCCESS;
    }
}
