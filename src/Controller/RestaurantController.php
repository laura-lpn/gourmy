<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Entity\Review;
use App\Form\RestaurantType;
use App\Repository\RestaurantRepository;
use App\Service\GeocodingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
        return $this->render('restaurant/dashboard.html.twig');
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

            $this->addFlash('success', 'Votre demande de création de restaurant a bien été envoyée.');

            return $this->redirectToRoute('app_restaurant_profile');
        }

        return $this->render('restaurant/create.html.twig', [
            'restaurantForm' => $form->createView(),
            'restaurant' => $restaurant,
        ]);
    }

    #[Route('/restaurateur/mon-restaurant', name: 'app_restaurant_profile')]
    public function restaurantProfile(RestaurantRepository $restaurantRepository): Response
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

        $restaurantid = $user->getRestaurant()->getId();

        $restaurant = $restaurantRepository->find($restaurantid);

        if (!$restaurant) {
            $this->addFlash('error', 'Restaurant introuvable.');
            return $this->redirectToRoute('app_restaurant_create');
        }

        $isValidated = $restaurant->isValided();
        if (!$isValidated) {
            $this->addFlash('warning', 'Votre restaurant n\'a pas encore été validé.');
        }

        return $this->render('restaurant/profile.html.twig', [
            'restaurant' => $restaurant,
            'isValidated' => $isValidated
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

        return $this->render('restaurant/show.html.twig', [
            'restaurant' => $restaurant,
            'isOwner' => $isOwner,
        ]);
    }

    #[Route('/restaurants', name: 'app_restaurant_list')]
    public function listRestaurant(RestaurantRepository $restaurantRepository): Response
    {
        $restaurants = $restaurantRepository->findAll();

        return $this->render('restaurant/list.html.twig', [
            'restaurants' => $restaurants,
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
