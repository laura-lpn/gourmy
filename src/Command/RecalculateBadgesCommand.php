<?php

namespace App\Command;

use App\Entity\User;
use App\Service\BadgeManager;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'badges:recalculate',
  description: 'Recalcule et attribue tous les badges à tous les utilisateurs existants'
)]
class RecalculateBadgesCommand extends Command
{
  public function __construct(
    private UserRepository $userRepo,
    private BadgeManager $badgeManager
  ) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $users = $this->userRepo->findAll();

    $io->title('Recalcul des badges pour tous les utilisateurs');

    $count = 0;
    foreach ($users as $user) {
      /** @var User $user */
      $this->badgeManager->checkAndGrantBadges($user);
      $count++;
    }

    $io->success("✅ Badges recalculés pour {$count} utilisateurs.");
    return Command::SUCCESS;
  }
}
