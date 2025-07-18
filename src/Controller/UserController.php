<?php

namespace App\Controller;

use App\Form\EditUserType;
use App\Repository\BadgeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

final class UserController extends AbstractController
{
    #[Route('/profil', name: 'app_user_profile')]
    public function index(BadgeRepository $badgeRepo, UserRepository $userRepo): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $photos = $user->getReviews()->filter(
            fn($review) => $review->getImageName() !== null
        )->toArray();

        $allUsers = $userRepo->findBy([], ['points' => 'DESC']);
        $rank = array_search($user, $allUsers, true) + 1;
        $allUsersCount = count($allUsers);

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'photos' => $photos,
            'all_badges' => $badgeRepo->findby([], ['id' => 'ASC']),
            'rank' => $rank,
            'all_users_count' => $allUsersCount,
            'favoriteRestaurants' => $user->getFavoriteRestaurants(),
            'favoriteRoadtrips' => $user->getFavoriteRoadtrips(),
        ]);
    }

    #[Route('/profil/modifier', name: 'app_user_edit')]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {

        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre profil a bien été modifier');
            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'userForm' => $form->createView(),
        ]);
    }

    #[Route('/profil/supprimer', name: 'app_user_delete')]
    public function delete(
        EntityManagerInterface $em,
        LogoutUrlGenerator $logoutUrlGenerator,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $tokenStorage->setToken(null);
        $session->invalidate();

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Votre compte a bien été supprimé');
        return $this->redirect($logoutUrlGenerator->getLogoutPath());
    }

    #[Route('/utilisateur/@{username}', name: 'app_user_show')]
    public function show(string $username, UserRepository $userRepository, BadgeRepository $badgeRepo): Response
    {
        $user = $userRepository->createQueryBuilder('u')
            ->where('LOWER(u.username) = :username')
            ->setParameter('username', strtolower($username))
            ->getQuery()
            ->getOneOrNullResult();

        if (!$user) {
            return $this->redirectToRoute('app_home');
        }

        $photos = $user->getReviews()->filter(
            fn($review) => $review->getImageName() !== null
        )->toArray();

        $allUsers = $userRepository->findBy([], ['points' => 'DESC']);
        $rank = array_search($user, $allUsers, true) + 1;
        $allUsersCount = count($allUsers);

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'photos' => $photos,
            'all_badges' => $badgeRepo->findBy([], ['id' => 'ASC']),
            'rank' => $rank,
            'all_users_count' => $allUsersCount,
        ]);
    }
}
