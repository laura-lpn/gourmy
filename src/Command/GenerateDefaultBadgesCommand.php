<?php

namespace App\Command;

use App\Entity\Badge;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'badges:generate-default',
  description: 'Génère les badges par défaut (avis, photos, roadtrips)'
)]
class GenerateDefaultBadgesCommand extends Command
{
  public function __construct(private EntityManagerInterface $em)
  {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);

    $badges = [
      // [Nom, Description, Type, Couleur, Condition]
      ["Petit Gourmet", "1 avis", "review", "#ffb74d", 1],
      ["Fin Palais", "5 avis", "review", "#ed8e06", 5],
      ["Maître Critique", "10 avis", "review", "#e65100", 10],

      ["Premier Cliché", "1 photo", "photo", "#ff8a80", 1],
      ["Photographe Culinaire", "5 photos", "photo", "#ff5757", 5],
      ["Artiste des Saveurs", "10 photos", "photo", "#b71c1c", 10],

      ["Explorateur", "1 roadtrip", "roadtrip", "#80cbc4", 1],
      ["Globe-Culinaire", "5 roadtrips", "roadtrip", "#07af91", 5],
      ["Épicurien Voyageur", "10 roadtrips", "roadtrip", "#004d40", 10],
    ];

    foreach ($badges as [$name, $desc, $type, $color, $value]) {
      $exists = $this->em->getRepository(Badge::class)->findOneBy(['name' => $name]);
      if ($exists) {
        continue; // évite les doublons
      }

      $badge = (new Badge())
        ->setName($name)
        ->setDescription($desc)
        ->setType($type)
        ->setBackgroundColor($color)
        ->setConditionValue($value);

      $this->em->persist($badge);
    }

    $this->em->flush();

    $io->success('✅ Badges par défaut générés avec succès !');
    return Command::SUCCESS;
  }
}
