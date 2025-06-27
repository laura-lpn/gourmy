<?php

namespace App\Controller\Api;

use App\Repository\RoadtripRepository;
use App\Repository\UserRepository;
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
                'restaurant' => $s->getRestaurant() ? [
                    'banner' => $baseUrl . '/uploads/restaurants/banners/' . $s->getRestaurant()->getBannerName(),
                ] : null
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
                'restaurant' => $s->getRestaurant() ? [
                    'banner' => $baseUrl . '/uploads/restaurants/banners/' . $s->getRestaurant()->getBannerName(),
                ] : null
            ], $r->getSteps()->toArray())
        ], $roadtrips);

        return $this->json($data);
    }
}
