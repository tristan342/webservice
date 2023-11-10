<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Form\MovieType;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/movie')]
class MovieController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private MovieRepository $movieRepository;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $entityManager,
                                MovieRepository $movieRepository,
                                ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->movieRepository = $movieRepository;
        $this->validator = $validator;
    }

    #[Route('', name: 'movie_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $movies = $this->movieRepository->findAll();
        return $this->json($movies, Response::HTTP_OK);
    }

    /**
     * @throws Exception
     */
    #[Route('', name: 'movie_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // Récupérer les données de la requête JSON
        $data = json_decode($request->getContent(), true);

        // Créer une nouvelle instance de Movie
        $movie = new Movie();
        $movie->setTitle($data['title']);
        $movie->setDescription($data['description']);
        $movie->setReleaseDate(new \DateTime($data['releaseDate']));
        if (isset($data['note'])) {
            $movie->setNote($data['note']);
        }
        if (isset($data['isUnderEightTeen'])) {
            $movie->setIsUnderEightTeen($data['isUnderEightTeen']);
        }
        if (isset($data['category'])) {
            $movie->setCategory($data['category']);
        }

        // Valider l'entité
        $errors = $this->validator->validate($movie);

        if (count($errors) > 0) {
            return $this->json(['message' => 'La validation a échoué.', 'errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Persister l'entité dans la base de données
        $this->entityManager->persist($movie);
        $this->entityManager->flush();

        return $this->json(['message' => 'Filme créé avec succès.', 'id' => $movie->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_movie_show', methods: ['GET'])]
    public function show(Movie $movie): JsonResponse
    {
        return $this->json($movie, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_movie_edit', methods: ['PUT'])]
    public function edit(Request $request, Movie $movie, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Mettre à jour les propriétés de l'entité Movie
        $movie->setTitle($data['title']);
        $movie->setDescription($data['description']);
        $movie->setReleaseDate(new \DateTime($data['releaseDate']));

        if (isset($data['note'])) {
            $movie->setNote($data['note']);
        }
        if (isset($data['isUnderEightTeen'])) {
            $movie->setIsUnderEightTeen($data['isUnderEightTeen']);
        }
        if (isset($data['category'])) {
            $movie->setCategory($data['category']);
        }

        $errors = $this->validator->validate($movie);

        if (count($errors) > 0) {
            return $this->json(['message' => 'La validation a échoué.', 'errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->flush();

        return $this->json(['message' => 'Filme mis à jour avec succès.'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_movie_delete', methods: ['DELETE'])]
    public function delete(Request $request, Movie $movie, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($movie);
        $entityManager->flush();

        return $this->json(['message' => $movie->getTitle() . ' supprimé avec succès.'], Response::HTTP_OK);
    }
}
