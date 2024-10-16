<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegisterController extends AbstractController

{
    public function __construct(private EmailVerifier $emailVerifier) {}

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function index(Request $request, SerializerInterface $serializer, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {

        $data = $request->getContent();
        $user = $serializer->deserialize($data, User::class, 'json');

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        if ($entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()])) {
            return new JsonResponse(['message' => 'User already exists'], 400);
        }
        if ($entityManager->getRepository(User::class)->findOneBy(['username' => $user->getUsername()])) {
            return new JsonResponse(['message' => 'Username already exists'], 400);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

        $entityManager->persist($user);
        $entityManager->flush();

        $this->emailVerifier->sendEmailConfirmation(
            'api_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address('gourmy@gmail.com', 'Gourmy'))
                ->to((string) $user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('email/confirmation_email.html.twig')
        );

        return new JsonResponse(['message' => 'Registration successful'], 201);
    }

    #[Route('/api/verify/email', name: 'api_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return new JsonResponse(['message' => 'Erreur lors de la vérification de l\'email'], 400);
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return new JsonResponse(['message' => 'Email vérifié'], 200);
    }
}
