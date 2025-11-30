<?php

namespace App\Controller;

use App\Entity\Roadtrip;
use App\Entity\Step;
use App\Repository\RestaurantRepository;
use App\Repository\RoadtripRepository;
use App\Repository\TypeRestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RoadtripController extends AbstractController
{
    #[Route('/roadtrips', name: 'app_roadtrips')]
    public function index(RoadtripRepository $roadtripRepository): Response
    {
        $roadtrips = $roadtripRepository->findBy(['isPublic' => true], ['id' => 'DESC']);
        $user = $this->getUser();

        return $this->render('roadtrips/index.html.twig', [
            'roadtrips' => $roadtrips,
            'userFavorites' => $user ? $user->getFavoriteRoadtrips()->toArray() : [],
        ]);
    }

    #[Route('/roadtrip/recherche', name: 'app_roadtrip_search')]
    public function search(Request $request, RestaurantRepository $repo): Response
    {
        $stepsInput = $request->query->all('steps');
        $results = [];
        $stepsForSave = [];

        foreach ($stepsInput as $index => $step) {
            $town = $step['town'];
            $cuisines = $step['cuisines'] ?? null;
            $meals = (int) ($step['meals'] ?? 1);

            $restaurants = $repo->findRandomByCriteria(
                $town,
                (!empty($cuisine) ? (is_array($cuisine) ? $cuisine : [$cuisine]) : null),
                $meals
            );

            $results[] = [
                'town' => $town,
                'meals' => $meals,
                'cuisines' => $cuisines,
                'restaurants' => $restaurants,
            ];

            $stepsForSave[] = [
                'town' => $town,
                'meals' => $meals,
                'cuisines' => $cuisines,
                'restaurantIds' => array_map(fn($r) => $r->getId(), $restaurants),
            ];
        }

        $user = $this->getUser();

        return $this->render('roadtrips/search_results.html.twig', [
            'results' => $results,
            'stepsForSave' => $stepsForSave,
            'userFavorites' => $user ? $user->getFavoriteRestaurants()->toArray() : [],
        ]);
    }

    #[Route('/roadtrips/save', name: 'app_roadtrip_save', methods: ['POST'])]
    public function save(
        Request $request,
        EntityManagerInterface $em,
        RestaurantRepository $repo,
        TypeRestaurantRepository $typeRepo
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour enregistrer un roadtrip.');
            return $this->redirectToRoute('app_login');
        }

        $stepsData = json_decode($request->request->get('stepsData'), true);
        $title = $request->request->get('title');
        $description = $request->request->get('description');
        $isPublic = $request->request->getBoolean('isPublic');

        $roadtrip = new Roadtrip();
        $roadtrip->setTitle($title)
            ->setDescription($description)
            ->setAuthor($user)
            ->setIsPublic($isPublic);

        foreach ($stepsData as $index => $stepData) {
            $step = new Step();
            $step->setTown($stepData['town']);
            $step->setMeals($stepData['meals'] ?? 1);
            $step->setPosition($index);

            // Ajouter les restaurants
            foreach ($stepData['restaurantIds'] ?? [] as $restoId) {
                $restaurant = $repo->find($restoId);
                if ($restaurant) {
                    $step->addRestaurant($restaurant);
                }
            }

            // Ajouter les types de cuisine
            $cuisineCodes = is_array($stepData['cuisine']) ? $stepData['cuisine'] : [$stepData['cuisine']];
            foreach ($cuisineCodes as $code) {
                if (!$code) continue;
                $type = $typeRepo->findOneBy(['name' => $code]);
                if ($type) {
                    $step->addCuisine($type);
                }
            }
            $step->setRoadtrip($roadtrip);
            $roadtrip->addStep($step);
        }

        $em->persist($roadtrip);
        $em->flush();

        $this->addFlash('success', 'Votre roadtrip a bien été enregistré.');
        return $this->redirectToRoute('app_roadtrip_show', ['id' => $roadtrip->getId()]);
    }

    // #[Route('/roadtrips/creer', name: 'app_roadtrip_create', methods: ['GET', 'POST'])]
    // public function create(
    //     Request $request,
    //     EntityManagerInterface $em,
    //     RestaurantRepository $restaurantRepo,
    //     TypeRestaurantRepository $typeRepo
    // ): Response {
    //     if (!$this->getUser()) {
    //         $this->addFlash('danger', 'Vous devez être connecté pour créer un roadtrip.');
    //         return $this->redirectToRoute('app_login');
    //     }

    //     if ($request->isMethod('POST')) {
    //         $data = json_decode($request->getContent(), true);
    //         $roadtrip = new Roadtrip();
    //         $roadtrip->setTitle($data['title'] ?? '')
    //             ->setDescription($data['description'] ?? '')
    //             ->setIsPublic($data['isPublic'] ?? false)
    //             ->setAuthor($this->getUser());

    //         foreach ($data['steps'] ?? [] as $index => $stepData) {
    //             $step = new Step();
    //             $step->setTown($stepData['town'] ?? '')
    //                 ->setPosition($index);

    //             if (isset($stepData['cuisine'])) {
    //                 $type = $typeRepo->findOneBy(['name' => $stepData['cuisine']]);
    //                 if ($type) {
    //                     $step->addCuisine($type);
    //                 }
    //             }

    //             foreach ($stepData['restaurantIds'] ?? [] as $restaurantId) {
    //                 $restaurant = $restaurantRepo->find($restaurantId);
    //                 if ($restaurant) {
    //                     $step->addRestaurant($restaurant);
    //                 }
    //             }

    //             $step->setRoadtrip($roadtrip);
    //             $roadtrip->addStep($step);
    //         }

    //         $em->persist($roadtrip);
    //         $em->flush();

    //         return $this->json(['success' => true, 'id' => $roadtrip->getId()]);
    //     }

    //     return $this->render('roadtrips/create.html.twig');
    // }

    #[Route('/roadtrips/{id}', name: 'app_roadtrip_show')]
    public function show(int $id, RoadtripRepository $roadtripRepository): Response
    {
        $roadtrip = $roadtripRepository->find($id);

        if (!$roadtrip) {
            throw $this->createNotFoundException('Roadtrip introuvable');
        }

        if (!$roadtrip->isPublic()) {
            $user = $this->getUser();
            if (!$user || $roadtrip->getAuthor() !== $user) {
                return $this->redirectToRoute('app_roadtrips');
            }
        }

        $user = $this->getUser();

        return $this->render('roadtrips/show.html.twig', [
            'roadtrip' => $roadtrip,
            'userFavorites' => $user ? $user->getFavoriteRestaurants()->toArray() : [],
            'isFavorite' => $user ? $user->hasFavoriteRoadtrip($roadtrip) : false,
        ]);
    }
}
