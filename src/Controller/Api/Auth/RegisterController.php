<?php

namespace App\Controller\Api\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        if ($entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]) || $entityManager->getRepository(User::class)->findOneBy(['username' => $user->getUsername()])) {
            return new JsonResponse(['message' => 'Nom d\'utilisateur ou adresse email déjà utilisé'], 400);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

        $entityManager->persist($user);
        $entityManager->flush();

        try {
            $this->emailVerifier->sendEmailConfirmation(
                'api_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('gourmy@gmail.com', 'Gourmy'))
                    ->to((string) $user->getEmail())
                    ->subject('Confirmez votre adresse email')
                    ->htmlTemplate('email/confirmation_email.html.twig')
            );
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Erreur lors de l\'envoi de l\'email de confirmation. Veuillez réessayer plus tard.'], 500);
        }

        return new JsonResponse(['message' => 'Inscription réussie, veuillez vérifier votre adresse email pour activer votre compte.'], 201);
    }

    #[Route('/api/verify/email', name: 'api_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): JsonResponse
    {
        $uid = $request->query->get('uid');

        if (!$uid) {
            return new JsonResponse(['message' => 'Uid non trouvé'], 404);
        }

        $user = $userRepository->findOneBy(['uuid' => $uid]);

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {

            return new JsonResponse(['message' => $translator->trans($exception->getReason(), [], 'VerifyEmailBundle')], 400);
        }

        return new JsonResponse(['message' => 'Email vérifié'], 200);
    }
}
