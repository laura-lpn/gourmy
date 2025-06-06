<?php

namespace App\Controller\Api;

use App\Entity\Review;
use App\Repository\RestaurantRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Vich\UploaderBundle\Handler\UploadHandler;

class ApiReviewController extends AbstractController
{
    #[Route('/api/reviews', name: 'api_review_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        RestaurantRepository $restaurantRepository,
        UploadHandler $uploadHandler
    ): JsonResponse {

        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], Response::HTTP_UNAUTHORIZED);
        }

        $title = $request->request->get('title');
        $comment = $request->request->get('comment');
        $rating = $request->request->get('rating');
        $restaurantId = $request->request->get('restaurant');
        $imageFile = $request->files->get('imageFile');

        $review = new Review();

        $review->setTitle($title);
        $review->setComment($comment);
        $review->setRating((float) $rating);
        $review->setAuthor($user);

        $restaurant = $restaurantRepository->find($restaurantId);
        if (!$restaurant) {
            return $this->json(['error' => 'Restaurant non trouvé.'], Response::HTTP_BAD_REQUEST);
        }
        $review->setRestaurant($restaurant);

        if ($imageFile) {
            $review->setImageFile($imageFile);
            $uploadHandler->upload($review, 'imageFile');
        }

        $errors = $validator->validate($review);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($review);
        $em->flush();

        return new JsonResponse(['message' => 'Avis envoyé avec succès.'], Response::HTTP_CREATED);
    }

    #[Route('/api/reviews/{id}', name: 'api_review_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em, ReviewRepository $reviewRepository): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], Response::HTTP_UNAUTHORIZED);
        }

        $review = $reviewRepository->find($id);
        if (!$review) {
            return new JsonResponse(['message' => 'Avis non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        if ($review->getAuthor() !== $user) {
            return new JsonResponse(['message' => 'Vous ne pouvez pas supprimer cet avis.'], Response::HTTP_FORBIDDEN);
        }

        $em->remove($review);
        $em->flush();

        return new JsonResponse(['message' => 'Avis supprimé avec succès.'], Response::HTTP_OK);
    }

    #[Route('/api/reviews/{id}', name: 'api_review_update', methods: ['POST'])]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        ReviewRepository $reviewRepository,
        ValidatorInterface $validator,
        UploadHandler $uploadHandler
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non authentifié.'], Response::HTTP_UNAUTHORIZED);
        }

        $review = $reviewRepository->find($id);
        if (!$review) {
            return new JsonResponse(['message' => 'Avis non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        if ($review->getAuthor() !== $user) {
            return new JsonResponse(['message' => 'Non autorisé.'], Response::HTTP_FORBIDDEN);
        }

        $title = $request->request->get('title');
        $comment = $request->request->get('comment');
        $rating = $request->request->get('rating');
        $imageFile = $request->files->get('imageFile');
        $deleteImage = $request->request->getBoolean('deleteImage');

        if (!$review->isResponse()) {
            $review->setTitle($title);
            $review->setRating((float) $rating);
        }

        $review->setComment($comment);

        if ($deleteImage) {
            $review->setImageFile(null);
        } elseif ($imageFile) {
            $review->setImageFile($imageFile);
            $uploadHandler->upload($review, 'imageFile');
        }

        $errors = $validator->validate($review);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->flush();

        return new JsonResponse(['message' => 'Avis mis à jour.'], Response::HTTP_OK);
    }

    #[Route('/api/restaurants/{id}/reviews', name: 'api_restaurant_reviews', methods: ['GET'])]
    public function listForRestaurant(
        int $id,
        Request $request,
        RestaurantRepository $restaurantRepository,
        ReviewRepository $reviewRepository
    ): JsonResponse {
        $restaurant = $restaurantRepository->find($id);

        if (!$restaurant) {
            return $this->json(['error' => 'Restaurant non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, (int) $request->query->get('limit', 10));
        $offset = ($page - 1) * $limit;

        // On récupère tous les avis du restaurant
        $allReviews = $reviewRepository->createQueryBuilder('r')
            ->leftJoin('r.author', 'a')
            ->leftJoin('r.response', 'resp')
            ->leftJoin('resp.author', 'ra')
            ->addSelect('a', 'resp', 'ra')
            ->where('r.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        // Filtrer les avis principaux (ceux qui ne sont pas des réponses)
        $mainReviews = array_filter($allReviews, fn(Review $r) => $r->getOriginalReview() === null);

        // Pagination manuelle
        $paginated = array_slice($mainReviews, $offset, $limit);

        $data = array_map(function (Review $review) {
            $response = $review->getResponse();

            return [
                'id' => $review->getId(),
                'title' => $review->getTitle(),
                'comment' => $review->getComment(),
                'rating' => $review->getRating(),
                'author' => [
                    'id' => $review->getAuthor()->getId(),
                    'username' => $review->getAuthor()->getUsername(),
                    'avatarName' => $review->getAuthor()->getAvatarName()
                ],
                'image' => $review->getImageName()
                    ? '/uploads/reviews/images/' . $review->getImageName()
                    : null,
                'createdAt' => $review->getCreatedAt()?->format('Y-m-d H:i:s'),
                'response' => $response ? [
                    'id' => $response->getId(),
                    'comment' => $response->getComment(),
                    'author' => $response->getAuthor()->getUsername(),
                    'createdAt' => $response->getCreatedAt()?->format('Y-m-d H:i:s'),
                ] : null,
            ];
        }, $paginated);

        return $this->json([
            'total' => count($mainReviews),
            'page' => $page,
            'limit' => $limit,
            'data' => $data,
        ]);
    }

    #[Route('/api/reviews/{id}/response', name: 'api_review_response', methods: ['POST'])]
    public function respondToReview(
        int $id,
        Request $request,
        ReviewRepository $reviewRepository,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user || !in_array('ROLE_RESTAURATEUR', $user->getRoles())) {
            return new JsonResponse(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $original = $reviewRepository->find($id);
        if (!$original) {
            return new JsonResponse(['message' => 'Avis non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($original->getResponse()) {
            return new JsonResponse(['message' => 'Réponse déjà enregistrée'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);
        $comment = $data['comment'] ?? '';

        if (empty($comment)) {
            return new JsonResponse(['message' => 'Commentaire requis'], Response::HTTP_BAD_REQUEST);
        }

        $response = new Review();
        $response->setComment($comment);
        $response->setAuthor($user);
        $response->setRestaurant($original->getRestaurant());
        $response->setOriginalReview($original);
        $original->setResponse($response);

        $errors = $validator->validate($response);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['message' => 'Validation échouée', 'errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        $em->persist($response);
        $em->flush();

        return $this->json([
            'comment' => $response->getComment(),
            'author' => $response->getAuthor()->getUsername()
        ]);
    }

    #[Route('/api/users/{id}/reviews', name: 'api_user_reviews', methods: ['GET'])]
    public function getUserReviews(
        int $id,
        Request $request,
        ReviewRepository $reviewRepository
    ): JsonResponse {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, (int) $request->query->get('limit', 10));
        $offset = ($page - 1) * $limit;

        // Récupérer tous les avis de l'utilisateur (hors réponses)
        $allReviews = $reviewRepository->createQueryBuilder('r')
            ->leftJoin('r.author', 'a')
            ->addSelect('a')
            ->where('r.author = :user')
            ->leftJoin('r.originalReview', 'orig')
            ->andWhere('orig.id IS NULL')
            ->setParameter('user', $id)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $paginated = array_slice($allReviews, $offset, $limit);

        $data = array_map(function (Review $review) {
            $response = $review->getResponse();

            return [
                'id' => $review->getId(),
                'title' => $review->getTitle(),
                'comment' => $review->getComment(),
                'rating' => $review->getRating(),
                'author' => [
                    'id' => $review->getAuthor()->getId(),
                    'username' => $review->getAuthor()->getUsername(),
                    'avatarName' => $review->getAuthor()->getAvatarName()
                ],
                'image' => $review->getImageName()
                    ? '/uploads/reviews/images/' . $review->getImageName()
                    : null,
                'createdAt' => $review->getCreatedAt()?->format('Y-m-d H:i:s'),
                'response' => $response ? [
                    'id' => $response->getId(),
                    'comment' => $response->getComment(),
                    'author' => $response->getAuthor()->getUsername(),
                    'createdAt' => $response->getCreatedAt()?->format('Y-m-d H:i:s'),
                ] : null,
            ];
        }, $paginated);

        return $this->json([
            'total' => count($allReviews),
            'page' => $page,
            'limit' => $limit,
            'data' => $data,
        ]);
    }
}
