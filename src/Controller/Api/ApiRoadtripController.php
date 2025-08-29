<?php

namespace App\Controller\Api;

use App\Repository\RoadtripRepository;
use App\Repository\UserRepository;
use App\Entity\Roadtrip;
use App\Entity\Step;
use App\Repository\RestaurantRepository;
use App\Repository\TypeRestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ApiRoadtripController extends AbstractController
{
    #[Route('/api/user/roadtrips', name: 'api_user_roadtrip_list', methods: ['GET'])]
    public function list(RoadtripRepository $repo, Request $request): JsonResponse
    {
        $user = $this->getUser();
        $roadtrips = $repo->findBy(['author' => $user]);

        $baseUrl = $request->getSchemeAndHttpHost();

        $data = array_map(fn($r) => [
            'id' => $r->getId(),
            'title' => $r->getTitle(),
            'description' => $r->getDescription(),
            'isPublic' => $r->isPublic(),
            'steps' => array_map(fn($s) => [
                'town' => $s->getTown(),
                'restaurants' => array_map(fn($r) => [
                    'name' => $r->getName(),
                    'banner' => $baseUrl . '/uploads/restaurants/banners/' . $r->getBannerName(),
                ], $s->getRestaurants()->toArray())
            ], $r->getSteps()->toArray())
        ], $roadtrips);

        return $this->json($data);
    }

    #[Route('/api/user/roadtrips/{id}', name: 'api_user_roadtrip_update', methods: ['PUT'])]
    public function update(int $id, Request $request, RoadtripRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $roadtrip = $repo->find($id);
        if (!$roadtrip || $roadtrip->getAuthor() !== $this->getUser()) {
            return $this->json(['error' => 'Non autorisé'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $roadtrip->setTitle($data['title'] ?? $roadtrip->getTitle());
        $roadtrip->setDescription($data['description'] ?? $roadtrip->getDescription());
        $roadtrip->setIsPublic($data['isPublic'] ?? $roadtrip->isPublic());

        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/user/roadtrips/{id}', name: 'api_user_roadtrip_delete', methods: ['DELETE'])]
    public function delete(int $id, RoadtripRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $roadtrip = $repo->find($id);
        if (!$roadtrip || $roadtrip->getAuthor() !== $this->getUser()) {
            return $this->json(['error' => 'Non autorisé'], 403);
        }

        $em->remove($roadtrip);
        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/user/{username}/roadtrips', name: 'api_user_roadtrip_public', methods: ['GET'])]
    public function publicRoadtrips(
        UserRepository $userRepo,
        RoadtripRepository $roadtripRepo,
        string $username,
        Request $request
    ): JsonResponse {
        $user = $userRepo->findOneBy(['username' => $username]);
        $baseUrl = $request->getSchemeAndHttpHost();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable'], Response::HTTP_NOT_FOUND);
        }

        $roadtrips = $roadtripRepo->findBy([
            'isPublic' => true,
            'author' => $user
        ]);

        $data = array_map(fn($r) => [
            'id' => $r->getId(),
            'title' => $r->getTitle(),
            'description' => $r->getDescription(),
            'isPublic' => $r->isPublic(),
            'steps' => array_map(fn($s) => [
                'town' => $s->getTown(),
                'restaurants' => array_map(fn($r) => [
                    'name' => $r->getName(),
                    'banner' => $baseUrl . '/uploads/restaurants/banners/' . $r->getBannerName(),
                ], $s->getRestaurants()->toArray())
            ], $r->getSteps()->toArray())
        ], $roadtrips);

        return $this->json($data);
    }

    // #[Route('/api/user/roadtrips/create', name: 'api_user_roadtrip_create', methods: ['POST'])]
    // public function createCustom(Request $request, EntityManagerInterface $em, RestaurantRepository $repo, TypeRestaurantRepository $typeRepo): JsonResponse
    // {
    //     $user = $this->getUser();
    //     if (!$user) {
    //         return $this->json(['error' => 'Non authentifié'], 401);
    //     }

    //     $data = json_decode($request->getContent(), true);

    //     $roadtrip = new Roadtrip();
    //     $roadtrip->setTitle($data['title'] ?? '');
    //     $roadtrip->setDescription($data['description'] ?? '');
    //     $roadtrip->setIsPublic($data['isPublic'] ?? false);
    //     $roadtrip->setAuthor($user);

    //     foreach ($data['steps'] ?? [] as $index => $stepData) {
    //         $step = new Step();
    //         $step->setTown($stepData['town'] ?? '');
    //         $step->setPosition($index);

    //         if (!empty($stepData['cuisine'])) {
    //             $type = $typeRepo->findOneBy(['name' => $stepData['cuisine']]);
    //             if ($type) {
    //                 $step->addCuisine($type);
    //             }
    //         }

    //         foreach ($stepData['restaurantIds'] ?? [] as $rid) {
    //             $r = $repo->find($rid);
    //             if ($r) {
    //                 $step->addRestaurant($r);
    //             }
    //         }

    //         $step->setRoadtrip($roadtrip);
    //         $roadtrip->addStep($step);
    //     }

    //     $em->persist($roadtrip);
    //     $em->flush();

    //     return $this->json(['success' => true]);
    // }

    #[Route('/api/user/roadtrips/favorites', name: 'api_user_roadtrip_favorites', methods: ['GET'])]
    public function favorites(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $baseUrl = $request->getSchemeAndHttpHost();
        $favorites = $user->getFavoriteRoadtrips();

        $data = array_map(fn($r) => [
            'id' => $r->getId(),
            'title' => $r->getTitle(),
            'description' => $r->getDescription(),
            'isPublic' => $r->isPublic(),
            'steps' => array_map(fn($s) => [
                'town' => $s->getTown(),
                'restaurants' => array_map(fn($r) => [
                    'name' => $r->getName(),
                    'banner' => $baseUrl . '/uploads/restaurants/banners/' . $r->getBannerName(),
                ], $s->getRestaurants()->toArray())
            ], $r->getSteps()->toArray())
        ], $favorites->toArray());

        return $this->json($data);
    }

    #[Route('/api/user/roadtrips/{id}/favorite', name: 'api_user_roadtrip_favorite_add', methods: ['POST'])]
    public function addFavorite(int $id, RoadtripRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $roadtrip = $repo->find($id);

        if (!$roadtrip || !$roadtrip->isPublic()) {
            return $this->json(['error' => 'Roadtrip non trouvé ou non public'], 404);
        }

        if ($user->hasFavoriteRoadtrip($roadtrip)) {
            return $this->json(['message' => 'Déjà en favoris']);
        }

        $user->addFavoriteRoadtrip($roadtrip);
        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/user/roadtrips/{id}/favorite', name: 'api_user_roadtrip_favorite_remove', methods: ['DELETE'])]
    public function removeFavorite(int $id, RoadtripRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $roadtrip = $repo->find($id);

        if (!$roadtrip) {
            return $this->json(['error' => 'Roadtrip non trouvé'], 404);
        }

        $user->removeFavoriteRoadtrip($roadtrip);
        $em->flush();

        return $this->json(['success' => true]);
    }
}
