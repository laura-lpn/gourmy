<?php

namespace App\Controller;

use App\Repository\RestaurantRepository;
use App\Repository\RoadtripRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RoadtripRepository $roadtripRepo, RestaurantRepository $restaurantRepo): Response
    {
        $roadtripsRecent = $roadtripRepo->findBy(['isPublic' => true], ['id' => 'DESC'], 10);
        $restaurants = $restaurantRepo->findBy(['isValided' => true], ['id' => 'DESC'], 8);
        $user = $this->getUser();

        return $this->render('public/index.html.twig', [
            'roadtripsRecent' => $roadtripsRecent,
            'restaurants' => $restaurants,
            'userFavoritesRestaurants' => $user ? $user->getFavoriteRestaurants()->toArray() : [],
            'userFavoritesRoadtrips' => $user ? $user->getFavoriteRoadtrips()->toArray() : [],
        ]);
    }

    #[Route('/charte-restaurateur', name: 'app_charte_restaurateur')]
    public function charteRestaurateur(): Response
    {
        return $this->render('public/charte_restaurateur.html.twig');
    }

    #[Route('/classement', name: 'app_user_leaderboard')]
    public function leaderboard(UserRepository $userRepo): Response
    {
        $users = $userRepo->findBy([], ['points' => 'DESC']);

        return $this->render('public/leaderboard.html.twig', [
            'users' => $users,
        ]);
    }
}
