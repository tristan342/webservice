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
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/movie')]
class MovieController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private MovieRepository $movieRepository;
    private ValidatorInterface $validator;
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager,
                                MovieRepository $movieRepository,
                                ValidatorInterface $validator,
                                SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->movieRepository = $movieRepository;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    #[Route('/', name: 'app_movie_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $movies = $this->movieRepository->findAll();

        $format = $request->headers->get('Accept', 'application/json');

        $data = $this->serializer->normalize($movies, null, [
            AbstractNormalizer::ATTRIBUTES => ['id', 'title', 'description', 'releaseDate', 'note', 'isUnderEightTeen', 'category'],
        ]);

        if ($format === 'application/xml') {
            $response = new Response($this->serializer->serialize($data, 'xml'), Response::HTTP_OK);
        } else {
            $response = new JsonResponse($data, Response::HTTP_OK);
        }

        return $response;
    }

    /**
     * @throws \Exception
     */
    #[Route('', name: 'movie_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        // Récupérer les données de la requête JSON
        $data = json_decode($request->getContent(), true);

        // Créer une nouvelle instance de Movie
        $movie = new Movie();
        $movie->setTitle($data['title']);
        $movie->setDescription($data['description']);
        $movie->setReleaseDate(new \DateTime($data['releaseDate']));

        // Définir les propriétés facultatives si elles sont présentes dans les données
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
            return $this->createValidationErrorResponse($errors);
        }

        // Persister l'entité dans la base de données
        $this->entityManager->persist($movie);
        $this->entityManager->flush();

        // Répondre en fonction de l'en-tête Accept
        $format = $request->headers->get('Accept', 'application/json');

        $data = $this->serializer->normalize($movie, null, [
            AbstractNormalizer::ATTRIBUTES => ['id', 'title', 'description', 'releaseDate', 'note', 'isUnderEightTeen', 'category'],
        ]);

        if ($format === 'application/xml') {
            $response = new Response($this->serializer->serialize($data, 'xml'), Response::HTTP_CREATED);
        } else {
            $response = new JsonResponse(['message' => 'Film créé avec succès.', 'data' => $data], Response::HTTP_CREATED);
        }

        return $response;
    }

    /**
     * Crée une réponse d'erreur de validation JSON ou XML
     */
    private function createValidationErrorResponse($errors): JsonResponse
    {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[$error->getPropertyPath()] = $error->getMessage();
        }

        return $this->json(['message' => 'La validation a échoué.', 'errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[Route('/{id}', name: 'app_movie_show', methods: ['GET'])]
    public function show(Movie $movie, Request $request): Response
    {
        // Récupérer le format souhaité à partir de l'en-tête Accept
        $format = $request->headers->get('Accept', 'application/json');

        // Normaliser les données du film
        $data = $this->serializer->normalize($movie, null, [
            AbstractNormalizer::ATTRIBUTES => ['id', 'title', 'description', 'releaseDate', 'note', 'isUnderEightTeen', 'category'],
        ]);

        // Créer la réponse en fonction du format
        if ($format === 'application/xml') {
            $response = new Response($this->serializer->serialize($data, 'xml'), Response::HTTP_OK);
        } else {
            $response = new JsonResponse(['data' => $data], Response::HTTP_OK);
        }

        return $response;
    }

    #[Route('/{id}', name: 'app_movie_edit', methods: ['PUT'])]
    public function edit(Request $request, Movie $movie): Response
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
            return $this->createValidationErrorResponse($errors);
        }

        $this->entityManager->flush();

        // Récupérer le format souhaité à partir de l'en-tête Accept
        $format = $request->headers->get('Accept', 'application/json');

        // Créer la réponse en fonction du format
        $data = ['message' => 'Film mis à jour avec succès.'];

        if ($format === 'application/xml') {
            $response = new Response($this->serializer->serialize($data, 'xml'), Response::HTTP_OK);
        } else {
            $response = new JsonResponse($data, Response::HTTP_OK);
        }

        return $response;
    }

    #[Route('/{id}', name: 'app_movie_delete', methods: ['DELETE'])]
    public function delete(Request $request, Movie $movie): Response
    {
        $this->entityManager->remove($movie);
        $this->entityManager->flush();

        // Récupérer le format souhaité à partir de l'en-tête Accept
        $format = $request->headers->get('Accept', 'application/json');

        // Créer la réponse en fonction du format
        $data = ['message' => $movie->getTitle() . ' supprimé avec succès.'];

        if ($format === 'application/xml') {
            $response = new Response($this->serializer->serialize($data, 'xml'), Response::HTTP_OK);
        } else {
            $response = new JsonResponse($data, Response::HTTP_OK);
        }

        return $response;
    }

    #Show all movies by category id
    #[Route('/{id}/categories', name: 'movie_categories', methods: ['GET'])]
    public function getCategoriesById(Movie $movie, Request $request): Response
    {

        $categories = $movie->getCategories();

        $format = $request->getPreferredFormat();

        if (!in_array($format, [JsonEncoder::FORMAT, XmlEncoder::FORMAT])) {
            $format = JsonEncoder::FORMAT; // default to JSON
        }

        $data = $this->serializer->normalize($categories, null, [
            AbstractNormalizer::ATTRIBUTES => ['id', 'name'],
        ]);

        $response = new Response($this->serializer->serialize($data, $format));
        $response->headers->set('Content-Type', $request->getMimeType($format));

        return $response;
    }

    #Add a category to a movie
    #[Route('/{id}/categories', name: 'movie_add_category', methods: ['POST'])]
    public function addCategory(Request $request, Movie $movie): Response
    {
        $format = $request->getPreferredFormat();

        if (!in_array($format, [JsonEncoder::FORMAT, XmlEncoder::FORMAT])) {
            $format = JsonEncoder::FORMAT; // default to JSON
        }

        $data = json_decode($request->getContent(), true);

        $category = $data['category'];

        $movie->addCategory($category);

        $this->entityManager->flush();

        $format = $request->headers->get('Accept', 'application/json');

        $data = ['message' => 'Catégorie ajoutée avec succès.'];

        $response = new Response($this->serializer->serialize($data, $format));
        $response->headers->set('Content-Type', $request->getMimeType($format));

        return $response;
    }

    #[Route('/search/{title?}/{description?}', name: 'movie_search', methods: ['GET'])]
    public function search(Request $request, ?string $title = null, ?string $description = null): Response
    {

        if ($title === null && $description === null) {
            $movies = $this->movieRepository->findAll();
        } else {
            $movies = $this->movieRepository->findMovies($title, $description);
        }

    return $this->json($movies);
}
}
