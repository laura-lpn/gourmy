<?php

namespace App\EventListener;

use App\Entity\Review;
use App\Entity\Roadtrip;
use App\Service\BadgeManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsDoctrineListener(event: Events::postPersist)]
class UserActivityListener
{
  public function __construct(private BadgeManager $badgeManager) {}

  public function postPersist(LifecycleEventArgs $args): void
  {
    $entity = $args->getObject();

    if ($entity instanceof Review || $entity instanceof Roadtrip) {
      $user = $entity->getAuthor();
      if (!$user) {
        return;
      }

      $points = 0;
      if ($entity instanceof Review) {
        $points += 1;
        if ($entity->getImageName()) {
          $points += 1; // bonus pour photo
        }
      }
      if ($entity instanceof Roadtrip) {
        $points += 2;
      }

      $user->addPoints($points);

      $em = $args->getObjectManager();
      $em->persist($user);
      $em->flush();

      $this->badgeManager->checkAndGrantBadges($user);
    }
  }
}
