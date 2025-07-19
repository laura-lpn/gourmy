<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Entity\Badge;
use App\Repository\RestaurantRepository;
use App\Repository\BadgeRepository;
use App\Repository\ReviewRepository;
use App\Repository\RoadtripRepository;
use App\Repository\UserRepository;
use App\Service\BadgeManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'app_admin_')]
final class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private BadgeManager $badgeManager
    ) {}

    #[Route('', name: 'dashboard')]
    public function index(RestaurantRepository $restaurantRepo, RoadtripRepository $roadtripRepo, UserRepository $userRepo, ReviewRepository $reviewRepo, BadgeRepository $badgeRepo): Response
    {

        $restaurantsValidated = $restaurantRepo->count(['isValided' => true]);
        $restaurantsPending = $restaurantRepo->count(['isValided' => false]);
        $roadtripsPublic = $roadtripRepo->count(['isPublic' => true]);
        $users = $userRepo->count([]);
        $badges = $badgeRepo->findAll();
        $reviews = $reviewRepo->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->leftJoin('r.originalReview', 'o')
            ->andWhere('o.id IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $topUsers = $userRepo->createQueryBuilder('u')
            ->orderBy('u.points', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return $this->render('admin/index.html.twig', [
            'restaurantsValidated' => $restaurantsValidated,
            'restaurantsPending' => $restaurantsPending,
            'roadtripsPublic' => $roadtripsPublic,
            'users' => $users,
            'reviews' => $reviews,
            'topUsers' => $topUsers,
            'badgesCount' => count($badges)
        ]);
    }

    #[Route('/restaurants', name: 'restaurants')]
    public function restaurants(RestaurantRepository $restaurantRepo): Response
    {
        $restaurants = $restaurantRepo->findBy(['isValided' => false]);

        return $this->render('admin/restaurants.html.twig', [
            'restaurants' => $restaurants
        ]);
    }

    #[Route('/restaurant/validate/{id}', name: 'restaurant_validate', methods: ['POST'])]
    public function validateRestaurant(Restaurant $restaurant): JsonResponse
    {
        $restaurant->setValided(true);
        $this->em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/restaurant/refuse/{id}', name: 'restaurant_refuse', methods: ['POST'])]
    public function refuseRestaurant(Restaurant $restaurant): JsonResponse
    {
        $this->em->remove($restaurant);
        $this->em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/badges', name: 'badges')]
    public function badges(BadgeRepository $badgeRepo): Response
    {
        $badges = $badgeRepo->findBy([], ['id' => 'ASC']);

        return $this->render('admin/badges.html.twig', [
            'badges' => $badges
        ]);
    }

    #[Route('/badges/recalculate', name: 'badges_recalculate', methods: ['POST'])]
    public function recalculateBadges(UserRepository $userRepo): JsonResponse
    {
        $users = $userRepo->findAll();
        foreach ($users as $user) {
            $this->badgeManager->checkAndGrantBadges($user);
        }
        return new JsonResponse(['success' => true, 'message' => 'Badges recalculés avec succès !']);
    }

    #[Route('/badges/delete/{id}', name: 'badges_delete', methods: ['POST'])]
    public function delete(Badge $badge, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($badge);
        $em->flush();

        return $this->json(['success' => true, 'message' => 'Badge supprimé avec succès !']);
    }

    #[Route('/badges/add', name: 'badge_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $badge = (new Badge())
            ->setName($data['name'])
            ->setType($data['type'])
            ->setDescription($data['description'])
            ->setBackgroundColor($data['backgroundColor'])
            ->setConditionValue((int)$data['conditionValue']);
        $em->persist($badge);
        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/badges/edit/{id}', name: 'badge_edit', methods: ['POST'])]
    public function edit(Badge $badge, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $badge
            ->setName($data['name'])
            ->setType($data['type'])
            ->setConditionValue((int)$data['conditionValue']);
        $em->flush();

        return $this->json(['success' => true]);
    }
}
