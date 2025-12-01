<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private ParameterBagInterface $params
    ) {}

    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $existingByEmail = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            $existingByUsername = $entityManager->getRepository(User::class)->findOneBy(['username' => $user->getUsername()]);

            if ($existingByEmail) {
                $form->addError(new FormError("Un compte avec ces identifiants existe déjà"));
            }
            if ($existingByUsername) {
                $form->addError(new FormError("Ce nom d'utilisateur est déjà utilisé"));
            }

            if ($form->isValid()) {
                $plainPassword = $form->get('plainPassword')->getData();
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

                $entityManager->persist($user);
                $entityManager->flush();

                $this->emailVerifier->sendEmailConfirmation(
                    'app_verify_email',
                    $user,
                    (new TemplatedEmail())
                        ->from(new Address('contact@asyafood.fr', 'Gourmy'))
                        ->to((string) $user->getEmail())
                        ->subject('Veuillez confirmer votre email')
                        ->htmlTemplate('email/confirmation_email.html.twig')
                        ->context([
                            'user' => $user,
                            'logo_cid' => 'gourmy_logo'
                        ])
                        ->embedFromPath(
                            $this->params->get('kernel.project_dir') . '/public/images/logo-gourmy.png',
                            'gourmy_logo'
                        )
                );

                $this->addFlash('success', 'Votre compte a bien été créé. Veuillez confirmer votre adresse email pour pouvoir vous connecter.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('auth/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verification/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository, TranslatorInterface $translator): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $uid = $request->query->get('uid');

        if (!$uid) {
            return $this->redirectToRoute('app_home');
        }

        $user = $userRepository->findOneBy(['uuid' => $uid]);

        // Ensure the user exists in persistence
        if (null === $user) {
            return $this->redirectToRoute('app_home');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
            $this->addFlash('success', 'Votre adresse email a bien été confirmée. Vous pouvez maintenant vous connecter.');
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        return $this->redirectToRoute('app_login');
    }
}
