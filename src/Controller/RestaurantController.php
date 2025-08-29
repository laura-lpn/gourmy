<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Entity\RestaurantCharter;
use App\Entity\Review;
use App\Form\RestaurantCharterType;
use App\Form\RestaurantType;
use App\Repository\RestaurantRepository;
use App\Repository\StepRepository;
use App\Service\GeocodingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class RestaurantController extends AbstractController
{
    #[Route('/restaurateur', name: 'app_restaurateur')]
    public function restaurateur(): Response
    {
        $user = $this->getUser();

        /** @var \App\Entity\User $user */
        if ($user && $user->getRestaurant()) {
            return $this->redirectToRoute('app_restaurant_profile');
        }
        return $this->render('restaurant/restaurateur.html.twig');
    }

    #[Route('/restaurateur/creer-un-restaurant', name: 'app_restaurant_create')]
    public function createRestaurant(Request $request, EntityManagerInterface $em, GeocodingService $geocodingService): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        /** @var \App\Entity\User $user */
        if (in_array('ROLE_RESTAURATEUR', $user->getRoles()) && $user->getRestaurant()) {
            return $this->redirectToRoute('app_restaurant_profile');
        }

        $restaurant = new Restaurant();
        $roles = array_unique(array_merge($user->getRoles(), ['ROLE_RESTAURATEUR']));
        $user->setRoles($roles);

        $form = $this->createForm(RestaurantType::class, $restaurant, [
            'allow_file_upload' => true,
            'csrf_protection' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fullAddress = sprintf(
                '%s, %s %s, %s',
                $restaurant->getAddress(),
                $restaurant->getPostalCode(),
                $restaurant->getCity(),
                $restaurant->getCountry()
            );

            $location = $geocodingService->geocode($fullAddress);

            if ($location) {
                $restaurant->setLatitude($location['lat']);
                $restaurant->setLongitude($location['lng']);
            } else {
                $this->addFlash('error', 'Impossible de géolocaliser cette adresse.');
                return $this->render('restaurant/create.html.twig', [
                    'restaurantForm' => $form->createView(),
                ]);
            }

            $em->persist($restaurant);
            $user->setRestaurant($restaurant);
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_restaurant_charter', [
                'id' => $restaurant->getId(),
            ]);
        }

        return $this->render('restaurant/create.html.twig', [
            'restaurantForm' => $form->createView(),
        ]);
    }

    #[Route('/restaurateur/restaurant/{id}/charte', name: 'app_restaurant_charter')]
    public function charter(Restaurant $restaurant, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user || $user->getRestaurant()?->getId() !== $restaurant->getId()) {
            $this->addFlash('error', 'Vous n’êtes pas autorisé à accéder à cette page.');
            return $this->redirectToRoute('app_restaurateur');
        }

        $charter = new RestaurantCharter();
        $charter->setRestaurant($restaurant);

        $form = $this->createForm(RestaurantCharterType::class, $charter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($charter);
            $em->flush();
            return $this->redirectToRoute('app_restaurant_profile');
        }

        return $this->render('restaurant/charter.html.twig', [
            'charterForm' => $form->createView(),
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/restaurateur/mon-restaurant', name: 'app_restaurant_profile')]
    public function restaurantProfile(
        RestaurantRepository $restaurantRepository,
        StepRepository $stepRepository,
        ChartBuilderInterface $chartBuilder
    ): Response {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user->getRestaurant()) {
            $this->addFlash('success', 'Votre demande de création de restaurant a bien été envoyée.');
            return $this->redirectToRoute('app_restaurant_create');
        }

        $restaurant = $restaurantRepository->find($user->getRestaurant()->getId());
        if (!$restaurant) {
            $this->addFlash('error', 'Restaurant introuvable.');
            return $this->redirectToRoute('app_restaurant_create');
        }

        $isValidated = $restaurant->isValided();

        // Filtrer uniquement les vrais avis clients (pas les réponses)
        $reviews = $restaurant->getReviews()->filter(fn($r) => !$r->isResponse());

        $nbReviews = count($reviews);
        $avgRating = $restaurant->getAverageRating() ?? 0;
        $nbFavorites = $restaurant->getUserFavorites()->count();
        $nbRoadtrips = $stepRepository->countByRestaurant($restaurant);

        // Répartition des notes
        $ratings = [0, 0, 0, 0, 0];
        foreach ($reviews as $review) {
            $r = (int)floor($review->getRating());
            if ($r >= 1 && $r <= 5) {
                $ratings[$r - 1]++;
            }
        }

        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => ['1★', '2★', '3★', '4★', '5★'],
            'datasets' => [[
                'label' => 'Répartition des notes',
                'backgroundColor' => ['#e11d48', '#7c3aed', '#098C8C', '#65a30d', '#ED8E06'],
                'borderColor' => '#050505',
                'data' => $ratings,
            ]],
        ]);
        $chart->setOptions(['responsive' => true, 'plugins' => ['legend' => ['display' => false]]]);

        // Évolution des avis par mois
        $reviewsByMonth = [];
        foreach ($reviews as $review) {
            $month = $review->getCreatedAt()->format('Y-m');
            $reviewsByMonth[$month] = ($reviewsByMonth[$month] ?? 0) + 1;
        }
        ksort($reviewsByMonth);

        $labels = array_keys($reviewsByMonth);
        $dataEvolution = array_values($reviewsByMonth);

        $chartEvolution = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chartEvolution->setData([
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Évolution des avis',
                'borderColor' => '#050505',
                'backgroundColor' => '#098C8C',
                'fill' => true,
                'tension' => 0.3,
                'data' => $dataEvolution,
                'borderWidth' => 0,
                'borderRadius' => 5,
                'spacing' => 5,
            ]],
        ]);
        $chartEvolution->setOptions([
            'responsive' => true,
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['stepSize' => 1]]],
        ]);

        // Donut chart Avis vs Favoris
        $chartDonut = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chartDonut->setData([
            'labels' => ['Avis', 'Mis en favoris', 'Roadtrips'],
            'datasets' => [[
                'data' => [$nbReviews, $nbFavorites, $nbRoadtrips],
                'backgroundColor' => ['#e11d48', '#098C8C', '#ED8E06'],
                'borderWidth' => 0,
                'borderRadius' => 5,
                'spacing' => 5,
            ]],
        ]);
        $chartDonut->setOptions([
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    "position" => "right",
                    "align" => "middle",
                    'labels' => [
                        'color' => '#050505',
                        'boxWidth' => 20,
                        'boxHeight' => 20,
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                    ],
                ],
            ],
        ]);

        // Stats récapitulatives
        $totalReviews = array_sum($dataEvolution);
        $averagePerMonth = $totalReviews > 0 ? round($totalReviews / count($dataEvolution), 1) : 0;
        $mostActiveMonth = null;
        $mostActiveCount = 0;
        $leastActiveMonth = null;
        $leastActiveCount = PHP_INT_MAX;

        foreach ($reviewsByMonth as $monthKey => $count) {
            if ($count > $mostActiveCount) {
                $mostActiveCount = $count;
                $mostActiveMonth = $monthKey;
            }
            if ($count < $leastActiveCount) {
                $leastActiveCount = $count;
                $leastActiveMonth = $monthKey;
            }
        }

        if ($leastActiveMonth === null) {
            $leastActiveCount = 0;
        }

        $mostActiveDate = $mostActiveMonth ? \DateTimeImmutable::createFromFormat('Y-m-d', $mostActiveMonth . '-01') : null;
        $leastActiveDate = $leastActiveMonth ? \DateTimeImmutable::createFromFormat('Y-m-d', $leastActiveMonth . '-01') : null;

        return $this->render('restaurant/profile.html.twig', [
            'restaurant' => $restaurant,
            'isValidated' => $isValidated,
            'nbReviews' => $nbReviews,
            'avgRating' => $avgRating,
            'nbFavorites' => $nbFavorites,
            'nbRoadtrips' => $nbRoadtrips,
            'chart' => $chart,
            'chartEvolution' => $chartEvolution,
            'chartDonut' => $chartDonut,
            'totalReviews' => $totalReviews,
            'averagePerMonth' => $averagePerMonth,
            'mostActiveDate'    => $mostActiveDate,
            'mostActiveCount'   => $mostActiveCount,
            'leastActiveDate'   => $leastActiveDate,
            'leastActiveCount'  => $leastActiveCount,
        ]);
    }

    #[Route('/restaurateur/mon-restaurant/modifier', name: 'app_restaurant_edit')]
    public function editRestaurant(Request $request, EntityManagerInterface $em, GeocodingService $geocodingService): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user->getRestaurant()) {
            $this->addFlash('error', 'Vous n\'avez pas de restaurant associé à votre compte.');
            return $this->redirectToRoute('app_restaurant_create');
        }

        $restaurant = $user->getRestaurant();
        $form = $this->createForm(RestaurantType::class, $restaurant, [
            'allow_file_upload' => true,
            'csrf_protection' => true
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fullAddress = sprintf(
                '%s, %s %s, %s',
                $restaurant->getAddress(),
                $restaurant->getPostalCode(),
                $restaurant->getCity(),
                $restaurant->getCountry()
            );

            $location = $geocodingService->geocode($fullAddress);

            if ($location) {
                $restaurant->setLatitude($location['lat']);
                $restaurant->setLongitude($location['lng']);
            }

            $em->flush();

            return $this->redirectToRoute('app_restaurant_profile');
        }

        return $this->render('restaurant/edit.html.twig', [
            'restaurantForm' => $form->createView(),
            'restaurant' => $restaurant
        ]);
    }

    #[Route('/restaurateur/mon-restaurant/supprimer', name: 'app_restaurant_delete')]
    public function deleteRestaurant(EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user->getRestaurant()) {
            $this->addFlash('error', 'Vous n\'avez pas de restaurant associé à votre compte.');
            return $this->redirectToRoute('app_restaurant_create');
        }

        $restaurant = $user->getRestaurant();
        $user->setRestaurant(null);

        $roles = $user->getRoles();
        $roles = array_filter($roles, fn($role) => $role !== 'ROLE_RESTAURATEUR');
        $user->setRoles($roles);

        $em->remove($restaurant);
        $em->flush();

        return $this->redirectToRoute('app_restaurateur');
    }

    #[Route('/restaurants/search', name: 'app_restaurant_search')]
    public function search(Request $request, RestaurantRepository $repository): Response
    {
        $query = trim($request->query->get('q', ''));

        $restaurants = $query
            ? $repository->searchByName($query)
            : $repository->findBy(['isValided' => true], ['name' => 'ASC']);

        return $this->render('restaurant/_list.html.twig', [
            'restaurants' => $restaurants,
        ]);
    }

    #[Route('/restaurants/{slug}', name: 'app_restaurant_show')]
    public function showRestaurant($slug, RestaurantRepository $restaurantRepository): Response
    {
        $restaurant = $restaurantRepository->findOneBy(['slug' => $slug]);

        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant introuvable.');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($user && $user->getRestaurant()) {
            $isOwner = $user->getRestaurant()->getId() === $restaurant->getId();
        } else {
            $isOwner = false;
        }

        $imagesReviews = array_filter(
            $restaurant->getReviews()->toArray(),
            fn($r) => $r->getImageName() !== null
        );

        return $this->render('restaurant/show.html.twig', [
            'restaurant' => $restaurant,
            'isOwner' => $isOwner,
            'imagesReviews' => $imagesReviews,
            'isFavorite' => $user ? $user->hasFavoriteRestaurant($restaurant) : false,
        ]);
    }

    #[Route('/restaurants', name: 'app_restaurant_list')]
    public function index(RestaurantRepository $restaurantRepository): Response
    {
        $restaurants = $restaurantRepository->findBy(['isValided' => true]);
        $user = $this->getUser();

        return $this->render('restaurant/list.html.twig', [
            'restaurants' => $restaurants,
            'userFavorites' => $user ? $user->getFavoriteRestaurants()->toArray() : [],
        ]);
    }

    #[Route('/commentaires/{id}/supprimer', name: 'app_review_delete')]
    public function deleteReview(Review $review, EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($user->getId() !== $review->getAuthor()->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer cet avis.');
            return $this->redirectToRoute('app_restaurant_show', ['slug' => $review->getRestaurant()->getSlug()]);
        } else {
            $em->remove($review);
            $em->flush();
            $this->addFlash('success', 'Votre avis a bien été supprimé');
        }
        return $this->redirectToRoute('app_restaurant_show', ['slug' => $review->getRestaurant()->getSlug()]);
    }
}
