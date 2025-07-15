<?php

namespace App\Controller\Api;

use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ApiRestaurantController extends AbstractController
{
    #[Route('/api/user/restaurants/favorites', name: 'api_user_restaurant_favorites', methods: ['GET'])]
    public function favorites(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $favorites = $user->getFavoriteRestaurants();

        $data = array_map(fn($r) => [
            'id' => $r->getId(),
            'slug' => $r->getSlug(),
            'description' => $r->getDescription(),
            'reviewsCount' => count(array_filter(
                $r->getReviews()->toArray(),
                fn($rr) => !$rr->getResponse()
            )),
            'name' => $r->getName(),
            'city' => $r->getCity(),
            'banner' => $r->getBannerName()
                ? '/uploads/restaurants/banners/' . $r->getBannerName()
                : null,
            'averageRating' => $r->getAverageRating(),
        ], $favorites->toArray());

        return $this->json($data);
    }
    
    #[Route('/api/user/restaurants/{id}/favorite', name: 'api_user_restaurant_favorite_add', methods: ['POST'])]
    public function addFavorite(
        int $id,
        RestaurantRepository $repo,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        $restaurant = $repo->find($id);

        if (!$restaurant) {
            return $this->json(['error' => 'Restaurant introuvable'], 404);
        }

        if ($user->hasFavoriteRestaurant($restaurant)) {
            return $this->json(['message' => 'Déjà en favoris']);
        }

        $user->addFavoriteRestaurant($restaurant);
        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/user/restaurants/{id}/favorite', name: 'api_user_restaurant_favorite_remove', methods: ['DELETE'])]
    public function removeFavorite(
        int $id,
        RestaurantRepository $repo,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        $restaurant = $repo->find($id);

        if (!$restaurant) {
            return $this->json(['error' => 'Restaurant introuvable'], 404);
        }

        $user->removeFavoriteRestaurant($restaurant);
        $em->flush();

        return $this->json(['success' => true]);
    }
}
