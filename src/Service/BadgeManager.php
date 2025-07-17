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

  public function checkAndGrantBadges(User $user): void
  {
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

  private function userMeetsBadgeCondition(User $user, $badge): bool
  {
    switch ($badge->getType()) {
      case 'review':
        return count($user->getReviews()) >= $badge->getConditionValue();
      case 'photo':
        $photoCount = array_reduce(
          $user->getReviews()->toArray(),
          fn($carry, $r) => $carry + ($r->getImageName() ? 1 : 0),
          0
        );
        return $photoCount >= $badge->getConditionValue();
      case 'roadtrip':
        return count($user->getRoadtrips()) >= $badge->getConditionValue();
    }
    return false;
  }

  private function sendBadgeEmail(User $user, $badge): void
  {
    $badgeImagePath = match ($badge->getType()) {
      'review' => 'images/badges/review.png',
      'photo' => 'images/badges/photo.png',
      'roadtrip' => 'images/badges/roadtrip.png'
    };

    $absolutePath = $this->params->get('kernel.project_dir') . '/public/' . $badgeImagePath;

    $email = (new TemplatedEmail())
      ->from(new Address('contact@gourmy.fr', 'Gourmy'))
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
