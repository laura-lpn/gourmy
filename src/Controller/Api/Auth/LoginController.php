<?php

namespace App\Controller\Api\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class LoginController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier) {}

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function apiLogin(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'Retente ta chance'], 401);
        }

        if (!$user->isVerified()) {
            return new JsonResponse(['message' => 'Veuillez confirmer votre adresse email pour vous connecter'], 403);
        }

        return new JsonResponse(['email' => $user->getEmail()], 200);
    }

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function apiMe(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'Retente ta chance'], 401);
        }

        return new JsonResponse(['email' => $user->getEmail()], 200);
    }

    #[Route('/api/resend-confirmation-email', name: 'api_resend_confirmation_email', methods: ['POST'])]
    public function apiResendConfirmationEmail(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (null === $data) {
            return new JsonResponse(['message' => 'Données invalides. Veuillez envoyer une requête JSON valide.'], 400);
        }
        if (empty($data['email'])) {
            throw new BadRequestHttpException('L\'adresse email est obligatoire.');
        }

        $email = $data['email'];
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse(['message' => 'Si un utilisateur est trouvé, un email de confirmation sera envoyé.'], 200);
        }

        if ($user->isVerified()) {
            return new JsonResponse(['message' => 'Cet utilisateur a déjà vérifié son adresse email.'], 200);
        }

        try {
            $this->emailVerifier->sendEmailConfirmation(
                'api_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('gourmy@gmail.com', 'Gourmy'))
                    ->to($user->getEmail())
                    ->subject('Confirmez votre adresse email')
                    ->htmlTemplate('email/confirmation_email.html.twig')
            );
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Erreur lors de l\'envoi de l\'email de confirmation. Veuillez réessayer plus tard.'], 500);
        }

        return new JsonResponse(['message' => 'Si un utilisateur est trouvé, un email de confirmation sera envoyé.'], 200);
    }
}
