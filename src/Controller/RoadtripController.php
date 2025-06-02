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
        $roadtrips = $roadtripRepository->findBy(['isPublic' => true]);

        return $this->render('roadtrips/index.html.twig', [
            'roadtrips' => $roadtrips,
        ]);
    }

    #[Route('/roadtrip/recherche', name: 'roadtrip_search')]
    public function search(Request $request, RestaurantRepository $repo): Response
    {
        $stepsInput = $request->query->all('steps');
        $results = [];
        $stepsForSave = [];

        foreach ($stepsInput as $index => $step) {
            $town = $step['town'];
            $cuisine = $step['cuisine'] ?? null;
            $meals = (int) ($step['meals'] ?? 1);

            $restaurants = $repo->findRandomByCriteria(
                $town,
                (!empty($cuisine) ? (is_array($cuisine) ? $cuisine : [$cuisine]) : null),
                $meals
            );

            $results[] = [
                'town' => $town,
                'meals' => $meals,
                'cuisine' => $cuisine,
                'restaurants' => $restaurants,
            ];

            $stepsForSave[] = array_map(fn($restaurant) => [
                'town' => $town,
                'meals' => $meals,
                'cuisine' => $cuisine,
                'restaurantId' => $restaurant->getId(),
            ], $restaurants);
        }

        return $this->render('roadtrips/search_results.html.twig', [
            'results' => $results,
            'stepsForSave' => $stepsForSave,
        ]);
    }

    #[Route('/roadtrips/save', name: 'roadtrip_save', methods: ['POST'])]
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

        foreach ($stepsData as $index => $stepGroup) {
            foreach ($stepGroup as $stepData) {
                $restaurant = $repo->find($stepData['restaurantId']);
                if (!$restaurant) continue;

                $step = new Step();
                $step->setTown($stepData['town']);
                $step->setMeals($stepData['meals'] ?? 1);
                $step->setPosition($index);
                $step->setRestaurant($restaurant);
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
        }

        $em->persist($roadtrip);
        $em->flush();

        $this->addFlash('success', 'Votre roadtrip a bien été enregistré.');
        return $this->redirectToRoute('app_roadtrip_show', ['id' => $roadtrip->getId()]);
    }

    #[Route('/roadtrips/{id}', name: 'app_roadtrip_show')]
    public function show(int $id, RoadtripRepository $roadtripRepository): Response
    {
        $roadtrip = $roadtripRepository->find($id);
        if (!$roadtrip) {
            throw $this->createNotFoundException('Roadtrip not found');
        }

        return $this->render('roadtrips/show.html.twig', [
            'roadtrip' => $roadtrip,
        ]);
    }
}
