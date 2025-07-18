<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\BadgeManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'badges:recalculate',
  description: 'Recalcule les points et attribue les badges pour tous les utilisateurs existants'
)]
class RecalculateBadgesCommand extends Command
{
  public function __construct(
    private UserRepository $userRepo,
    private BadgeManager $badgeManager,
    private EntityManagerInterface $em
  ) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $users = $this->userRepo->findAll();

    $io->title('🔄 Recalcul des points et des badges pour tous les utilisateurs');

    $countUsers = 0;
    foreach ($users as $user) {
      /** @var User $user */
      $oldPoints = $user->getPoints();

      // ✅ Recalcule points + badges
      $this->badgeManager->checkAndGrantBadges($user);

      $newPoints = $user->getPoints();
      $badgesCount = count($user->getBadges());

      $io->text(sprintf(
        "👤 %s (%s) : %d → %d points | %d badge(s)",
        $user->getUsername(),
        $user->getEmail(),
        $oldPoints,
        $newPoints,
        $badgesCount
      ));
      $countUsers++;
    }

    $this->em->flush();

    $io->success("✅ Recalcul terminé pour {$countUsers} utilisateur(s).");
    return Command::SUCCESS;
  }
}
