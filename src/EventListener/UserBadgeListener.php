<?php

namespace App\EventListener;

use App\Entity\Review;
use App\Entity\Roadtrip;
use App\Service\BadgeManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsDoctrineListener(event: Events::postPersist)]
class UserBadgeListener
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

      $this->badgeManager->checkAndGrantBadges($user);
    }
  }
}
