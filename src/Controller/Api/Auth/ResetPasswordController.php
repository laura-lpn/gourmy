<?php

namespace App\Controller\Api\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/api/forgot-password', name: 'api_forgot_password', methods: ['POST'])]
    public function apiForgotPassword(Request $request, MailerInterface $mailer, TranslatorInterface $translator, UserRepository $userRepository, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (null === $data) {
            return new JsonResponse(['message' => 'Données invalides. Veuillez envoyer une requête JSON valide.'], 400);
        }
        if (empty($data['email'])) {
            throw new BadRequestHttpException('L\'adresse email est obligatoire.');
        }

        $email = $data['email'];

        $errors = $this->validateEmail($email, $validator);
        if (!empty($errors)) {
            return new JsonResponse(['message' => implode(', ', $errors)], 400);
        }


        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse(['message' => 'Si un utilisateur est trouvé, un email de réinitialisation de mot de passe sera envoyé.'], 200);
        }

        $this->processSendingPasswordResetEmail(
            $email,
            $mailer,
            $translator
        );

        return new JsonResponse(['message' => 'Un email de réinitialisation de mot de passe a été envoyé.'], 200);
    }
    #[Route('/api/reset-password/{token}', name: 'api_reset_password', methods: ['POST'])]
    public function apiResetPassword(Request $request, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, ?string $token = null): JsonResponse
    {
        if ($token) {
            $this->storeTokenInSession($token);
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            return new JsonResponse(['message' => 'Token invalide'], 400);
        }

        $data = json_decode($request->getContent(), true);
        if (null === $data) {
            return new JsonResponse(['message' => 'Données invalides. Veuillez envoyer une requête JSON valide.'], 400);
        }
        if (empty($data['newPassword'])) {
            throw new BadRequestHttpException('Le nouveau mot de passe est obligatoire.');
        }

        $newPassword = $data['newPassword'];

        $errors = $this->validatePassword($newPassword, $validator);
        if (!empty($errors)) {
            return new JsonResponse(['message' => implode(', ', $errors)], 400);
        }

        try {
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            return new JsonResponse(['reset_password_error', sprintf(
                '%s - %s',
                $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, [], 'ResetPasswordBundle'),
                $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            )], 400);
        }

        $this->resetPasswordHelper->removeResetRequest($token);

        // Encode(hash) the plain password, and set it.
        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $this->entityManager->flush();

        // The session is cleaned up after the password has been changed.
        $this->cleanSessionAfterReset();

        return new JsonResponse(['message' => 'Mot de passe réinitialisé avec succès'], 200);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            return new JsonResponse(['message' => 'Si un utilisateur est trouvé, un email de réinitialisation de mot de passe sera envoyé.'], 200);
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {

            return new JsonResponse(['message' => $translator->trans($e->getReason())], 500);
        }

        $email = (new TemplatedEmail())
            ->from(new Address('gourmy@gmail.com', 'Gourmy'))
            ->to((string) $user->getEmail())
            ->subject('Réinitialisation de mot de passe')
            ->htmlTemplate('email/reset_password_email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);

        $mailer->send($email);

        $this->setTokenObjectInSession($resetToken);

        return new JsonResponse(['message' => 'Si un utilisateur est trouvé, un email de réinitialisation de mot de passe sera envoyé.'], 200);
    }

    private function validateEmail(string $email, ValidatorInterface $validator): array
    {
        $emailConstraint = new Assert\Email();
        $emailConstraint->message = "L'adresse email '{{ value }}' n'est pas valide.";

        $violations = $validator->validate($email, [new Assert\NotBlank(), $emailConstraint]);

        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = $violation->getMessage();
        }

        return $errors;
    }
    private function validatePassword(string $password, ValidatorInterface $validator): array
    {
        // Définition des contraintes de validation
        $violations = $validator->validate($password, [
            new Assert\NotBlank(),
            new Assert\Length([
                'min' => 8,
                'max' => 64,
                'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                'maxMessage' => 'Le mot de passe ne doit pas dépasser {{ limit }} caractères.'
            ]),
            new Assert\Regex([
                'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/',
                'message' => 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial.'
            ])
        ]);

        // Collecte des erreurs
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = $violation->getMessage();
        }

        return $errors;
    }
}
