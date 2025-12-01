<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\BadgeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mime\Address;

class BadgeManager
{
  public function __construct(
    private BadgeRepository $badgeRepo,
    private EntityManagerInterface $em,
    private MailerInterface $mailer,
    private ParameterBagInterface $params
  ) {}

  /**
   * Recalcule les points de l'utilisateur et attribue les badges débloqués
   */
  public function checkAndGrantBadges(User $user): void
  {
    // Recalcul complet des points
    $user->setPoints($this->calculateUserPoints($user));
    $this->em->persist($user);

    $badges = $this->badgeRepo->findAll();

    foreach ($badges as $badge) {
      if ($user->hasBadge($badge)) {
        continue;
      }

      if ($this->userMeetsBadgeCondition($user, $badge)) {
        $user->addBadge($badge);
        $this->em->persist($user);
        $this->sendBadgeEmail($user, $badge);
      }
    }

    $this->em->flush();
  }

  /**
   * Calcule les points d'un utilisateur en fonction de ses reviews, photos et roadtrips
   */
  private function calculateUserPoints(User $user): int
  {
    $reviewsCount = count($user->getReviews());
    $photosCount = array_reduce(
      $user->getReviews()->toArray(),
      fn($carry, $r) => $carry + ($r->getImageName() ? 1 : 0),
      0
    );
    $roadtripsCount = count($user->getRoadtrips());

    return ($reviewsCount * 1) + ($photosCount * 1) + ($roadtripsCount * 2);
  }

  /**
   * Vérifie si l'utilisateur respecte la condition pour un badge
   */
  private function userMeetsBadgeCondition(User $user, $badge): bool
  {
    return match ($badge->getType()) {
      'review' => count($user->getReviews()) >= $badge->getConditionValue(),
      'photo' => array_reduce(
        $user->getReviews()->toArray(),
        fn($carry, $r) => $carry + ($r->getImageName() ? 1 : 0),
        0
      ) >= $badge->getConditionValue(),
      'roadtrip' => count($user->getRoadtrips()) >= $badge->getConditionValue(),
      default => false
    };
  }

  /**
   * Envoie un email lorsque l'utilisateur débloque un badge
   */
  private function sendBadgeEmail(User $user, $badge): void
  {
    $badgeImagePath = match ($badge->getType()) {
      'review' => 'images/badges/review.png',
      'photo' => 'images/badges/photo.png',
      'roadtrip' => 'images/badges/roadtrip.png'
    };

    $absolutePath = $this->params->get('kernel.project_dir') . '/public/' . $badgeImagePath;

    $email = (new TemplatedEmail())
      ->from(new Address('contact@asyafood.fr', 'Gourmy'))
      ->to($user->getEmail())
      ->subject('Nouveau badge débloqué !')
      ->htmlTemplate('email/badge_won.html.twig')
      ->context([
        'user' => $user,
        'badge' => $badge,
        'badge_image_cid' => 'badge_image_' . $badge->getId(),
        'logo_cid' => 'gourmy_logo'
      ])
      ->embedFromPath($absolutePath, 'badge_image_' . $badge->getId())
      ->embedFromPath(
        $this->params->get('kernel.project_dir') . '/public/images/logo-gourmy.png',
        'gourmy_logo'
      );

    $this->mailer->send($email);
  }
}
