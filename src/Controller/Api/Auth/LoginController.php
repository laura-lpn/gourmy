<?php

namespace App\Controller\Api\Auth;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class LoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function apiLogin(#[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return $this->json(['message' => 'Retente ta chance'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->isVerified()) {
            return $this->json(['message' => 'Veuillez confirmer votre adresse email'], Response::HTTP_FORBIDDEN);
        }

        return $this->json(['email' => $user->getEmail()], Response::HTTP_OK);
    }

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(#[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return $this->json(['message' => 'Retente ta chance'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json(['email' => $user->getEmail()], Response::HTTP_OK);
    }
}
