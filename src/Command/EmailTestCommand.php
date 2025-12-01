<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Badge;
use App\Repository\UserRepository;
use App\Repository\BadgeRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mime\Address;

#[AsCommand(
  name: 'email:test',
  description: 'Envoie un email de test (badge, confirmation, reset) à une adresse donnée'
)]
class EmailTestCommand extends Command
{
  public function __construct(
    private MailerInterface $mailer,
    private UserRepository $userRepo,
    private BadgeRepository $badgeRepo,
    private ParameterBagInterface $params
  ) {
    parent::__construct();
  }

  protected function configure(): void
  {
    $this
      ->addArgument('email', InputArgument::REQUIRED, 'Adresse email où envoyer les tests')
      ->addArgument('type', InputArgument::OPTIONAL, 'Type d’email : badge|confirm|reset|all', 'all');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $email = $input->getArgument('email');
    $type = $input->getArgument('type');

    $io->title("Envoi d'un email de test ($type) à $email");

    // On prend un user existant pour tester
    $user = $this->userRepo->findOneBy([]);
    if (!$user) {
      $io->error('Aucun utilisateur en base pour tester.');
      return Command::FAILURE;
    }

    $logoPath = $this->params->get('kernel.project_dir') . '/public/images/logo-gourmy.png';

    if ($type === 'badge' || $type === 'all') {
      $badge = $this->badgeRepo->findOneBy([]);
      if (!$badge) {
        $io->warning("Aucun badge trouvé en base, test badge ignoré.");
      } else {
        $this->sendBadgeEmail($user, $badge, $email, $logoPath);
        $io->success("✅ Email badge envoyé !");
      }
    }

    if ($type === 'confirm' || $type === 'all') {
      $this->sendConfirmEmail($user, $email, $logoPath);
      $io->success("✅ Email confirmation envoyé !");
    }

    if ($type === 'reset' || $type === 'all') {
      $this->sendResetEmail($user, $email, $logoPath);
      $io->success("✅ Email reset envoyé !");
    }

    $io->success('🎉 Tous les tests terminés.');
    return Command::SUCCESS;
  }

  private function sendBadgeEmail(User $user, Badge $badge, string $toEmail, string $logoPath): void
  {
    $badgeImagePath = $this->params->get('kernel.project_dir') . '/public/images/badges/review.png';

    $email = (new TemplatedEmail())
      ->from(new Address('contact@asyafood.fr', 'Gourmy'))
      ->to($toEmail)
      ->subject('🎖 Nouveau badge débloqué ! (TEST)')
      ->htmlTemplate('email/badge_won.html.twig')
      ->context([
        'user' => $user,
        'badge' => $badge,
        'badge_image_cid' => 'badge_image_' . $badge->getId(),
        'logo_cid' => 'gourmy_logo'
      ])
      ->embedFromPath($badgeImagePath, 'badge_image_' . $badge->getId())
      ->embedFromPath($logoPath, 'gourmy_logo');

    $this->mailer->send($email);
  }

  private function sendConfirmEmail(User $user, string $toEmail, string $logoPath): void
  {
    $email = (new TemplatedEmail())
      ->from(new Address('contact@asyafood.fr', 'Gourmy'))
      ->to($toEmail)
      ->subject('Veuillez confirmer votre email (TEST)')
      ->htmlTemplate('email/confirmation_email.html.twig')
      ->context([
        'user' => $user,
        'signedUrl' => '#',
        'expiresAtMessageKey' => 'dans 1 heure',
        'expiresAtMessageData' => [],
        'logo_cid' => 'gourmy_logo'
      ])
      ->embedFromPath($logoPath, 'gourmy_logo');

    $this->mailer->send($email);
  }

  private function sendResetEmail(User $user, string $toEmail, string $logoPath): void
  {
    $email = (new TemplatedEmail())
      ->from(new Address('contact@asyafood.fr', 'Gourmy'))
      ->to($toEmail)
      ->subject('Réinitialisation du mot de passe (TEST)')
      ->htmlTemplate('email/email_reset.html.twig')
      ->context([
        'user' => $user,
        'resetToken' => (object)[
          'token' => 'fake_token_test',
          'expirationMessageKey' => 'dans 30 minutes',
          'expirationMessageData' => []
        ],
        'logo_cid' => 'gourmy_logo'
      ])
      ->embedFromPath($logoPath, 'gourmy_logo');

    $this->mailer->send($email);
  }
}
