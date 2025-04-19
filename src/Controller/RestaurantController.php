<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Form\CreateRestaurantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class RestaurantController extends AbstractController
{
    #[Route('/restaurateur', name: 'app_restaurateur')]
    public function restaurateur(): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('restaurant/index.html.twig');
    }

    #[Route('/restaurateur/mon-restaurant', name: 'app_restaurant')]
    public function restaurant(): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('restaurant/restaurant_profile.html.twig');
    }

    #[Route('/restaurateur/creer-un-restaurant', name: 'app_restaurant_create')]
    public function createRestaurant(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (in_array('ROLE_RESTAURATEUR', $user->getRoles())) {
            return $this->redirectToRoute('app_restaurateur');
        }

        if ($user->getRestaurant()) {
            return $this->redirectToRoute('app_restaurant');
        }

        $restaurant = new Restaurant();
        $form = $this->createForm(CreateRestaurantType::class, $restaurant);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $restaurant = $form->getData();
            $restaurant->setValided(false);
            $slug = $slugger->slug($restaurant->getName())->lower();
            $restaurant->setSlug($slug);
            $restaurant->setOwner($user);

            $roles = array_unique(array_merge($user->getRoles(), ['ROLE_RESTAURATEUR']));
            $user->setRoles($roles);

            $entityManager->persist($restaurant);
            $user->setRestaurant($restaurant);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre demande de création de restaurant a bien été envoyée. Elle sera validée après vérification des informations.');

            return $this->redirectToRoute('app_restaurateur');
        }

        return $this->render('restaurant/restaurant_create.html.twig', [
            'restaurantForm' => $form,
        ]);
    }
}
