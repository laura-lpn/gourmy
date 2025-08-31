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

    #[Route('/mentions-legales', name: 'app_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('legals/mentions.html.twig');
    }

    #[Route('/cgu', name: 'app_cgu')]
    public function cgu(): Response
    {
        return $this->render('legals/cgu.html.twig');
    }

    #[Route('/cgv', name: 'app_cgv')]
    public function cgv(): Response
    {
        return $this->render('legals/cgv.html.twig');
    }

    #[Route('/politique-de-confidentialite', name: 'app_politique_confidentialite')]
    public function politiqueConfidentialite(): Response
    {
        return $this->render('legals/politique_confidentialite.html.twig');
    }
}
