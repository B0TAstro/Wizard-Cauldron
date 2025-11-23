<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:promote-admin',
    description: 'Promote (or demote with --demote) a user to ROLE_ADMIN by email or username (pseudo).',
)]
class UserPromoteAdminCommand extends Command
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('identifier', InputArgument::REQUIRED, 'User email or username (pseudo)')
            ->addOption('demote', null, InputOption::VALUE_NONE, 'Remove admin role instead of adding it');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = (string) $input->getArgument('identifier');
        $demote = (bool) $input->getOption('demote');

        $user = $this->users->findOneBy(['email' => $id]) ?? $this->users->findOneBy(['pseudo' => $id]);

        if (!$user) {
            $io->error(sprintf('No user found with email or username "%s".', $id));
            return Command::FAILURE;
        }

        $roles = $user->getRoles();
        $hasAdmin = \in_array('ROLE_ADMIN', $roles, true);

        if ($demote) {
            if (!$hasAdmin) {
                $io->warning(sprintf('User "%s" is not an admin.', $id));
                return Command::SUCCESS;
            }
            $roles = array_values(array_filter($roles, fn(string $r) => $r !== 'ROLE_ADMIN'));
            $user->setRoles($roles);
            $this->em->flush();

            $io->success(sprintf('User "%s" has been demoted (ROLE_ADMIN removed).', $id));
            return Command::SUCCESS;
        }

        if ($hasAdmin) {
            $io->note(sprintf('User "%s" is already an admin.', $id));
            return Command::SUCCESS;
        }

        $roles[] = 'ROLE_ADMIN';
        $user->setRoles($roles);
        $this->em->flush();

        $io->success(sprintf('User "%s" promoted to admin (ROLE_ADMIN added).', $id));
        return Command::SUCCESS;
    }
}
