<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Form\ResendConfirmationEmailType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier) {}

    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, UserRepository $userRepository): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $user = $userRepository->findOneBy(['email' => $lastUsername]);

        if ($error) {
            $user = $userRepository->findOneBy(['email' => $lastUsername]);

            if ($user && !$user->isVerified()) {
                $this->addFlash('error', 'L\'email ou le mot de passe est incorrect. Si votre email n\'est pas encore confirmé, vous pouvez <a href="' . $this->generateUrl('app_resend_confirmation_email', ['email' => $lastUsername]) . '">renvoyer l\'email de confirmation ici</a>.');
            } else {
                $this->addFlash('error', 'L\'email ou le mot de passe est incorrect.');
            }
        }

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername
        ]);
    }

    #[Route('/renvoie-confirmation-email', name: 'app_resend_confirmation_email')]
    public function resendConfirmationEmail(Request $request, UserRepository $userRepository): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        $email = $request->query->get('email', '');

        $form = $this->createForm(ResendConfirmationEmailType::class, ['email' => $email]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $userRepository->findOneBy(['email' => $data['email']]);

            if ($user instanceof User && !$user->isVerified()) {
                $this->emailVerifier->sendEmailConfirmation(
                    'app_verify_email',
                    $user,
                    (new TemplatedEmail())
                        ->from(new Address('contact@gourmy.travel', 'Gourmy'))
                        ->to((string) $user->getEmail())
                        ->subject('Veuillez confirmer votre adresse email')
                        ->htmlTemplate('email/confirmation_email.html.twig')
                );
                $this->addFlash('success', 'Si un compte avec cet email existe, un email de confirmation sera renvoyé.');
            } else {
                $this->addFlash('error', 'Aucun compte trouvé avec cette adresse email ou le compte est déjà confirmé.');
            }
        }

        return $this->render('auth/resend_confirmation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Cette méthode peut rester vide. Elle est interceptée par le firewall.');
    }
}
