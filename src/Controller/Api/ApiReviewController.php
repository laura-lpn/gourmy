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

    #[Route('/api/restaurants/{id}/reviews', name: 'api_restaurant_reviews', methods: ['GET'])]
    public function listForRestaurant(int $id, Request $request, RestaurantRepository $restaurantRepository, ReviewRepository $reviewRepository): JsonResponse
    {
        $restaurant = $restaurantRepository->find($id);

        if (!$restaurant) {
            return $this->json(['error' => 'Restaurant non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, (int) $request->query->get('limit', 10));
        $offset = ($page - 1) * $limit;

        $qb = $reviewRepository->createQueryBuilder('r')
            ->leftJoin('r.response', 'resp')
            ->leftJoin('r.originalReview', 'original')
            ->addSelect('resp')
            ->where('r.restaurant = :restaurant')
            ->andWhere('original IS NULL')
            ->orderBy('r.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('restaurant', $restaurant);

        $total = $reviewRepository->createQueryBuilder('r')
            ->leftJoin('r.originalReview', 'original')
            ->select('COUNT(r.id)')
            ->where('r.restaurant = :restaurant')
            ->andWhere('original IS NULL')
            ->setParameter('restaurant', $restaurant)
            ->getQuery()
            ->getSingleScalarResult();


        $reviews = $qb->getQuery()->getResult();

        $data = array_map(function ($review) {
            return [
                'id' => $review->getId(),
                'title' => $review->getTitle(),
                'comment' => $review->getComment(),
                'rating' => $review->getRating(),
                'author' => $review->getAuthor()->getUsername(),
                'image' => $review->getImageName()
                    ? '/uploads/reviews/' . $review->getImageName()
                    : null,
                'createdAt' => $review->getCreatedAt()?->format('Y-m-d H:i:s'),
                'response' => $review->getResponse() ? [
                    'id' => $review->getResponse()->getId(),
                    'comment' => $review->getResponse()->getComment(),
                    'author' => $review->getResponse()->getAuthor()->getUsername(),
                    'createdAt' => $review->getResponse()->getCreatedAt()?->format('Y-m-d H:i:s'),
                ] : null
            ];
        }, $reviews);

        return $this->json([
            'total' => (int) $total,
            'page' => $page,
            'limit' => $limit,
            'data' => $data,
        ]);
    }
}
