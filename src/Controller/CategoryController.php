<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;


#[Route('/category')]
class CategoryController extends AbstractController
{

    private $serializer;
    private $entityManager;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'category_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $categories = $this->entityManager->getRepository(Category::class)->findAll();

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

    #[Route('/{id}', name: 'category_show', methods: ['GET'])]
    public function show(Category $category, Request $request): Response
    {
        $format = $request->getPreferredFormat();

        if (!in_array($format, [JsonEncoder::FORMAT, XmlEncoder::FORMAT])) {
            $format = JsonEncoder::FORMAT; // default to JSON
        }

        $data = $this->serializer->normalize($category, null, [
            AbstractNormalizer::ATTRIBUTES => ['id', 'name'],
        ]);

        $response = new Response($this->serializer->serialize($data, $format));
        $response->headers->set('Content-Type', $request->getMimeType($format));

        return $response;
    }

    #[Route('/{id}/movies', name: 'category_movies', methods: ['GET'])]
    public function getMoviesById(Category $category, Request $request/*, PagerfantaFactory $pagerfantaFactory*/): Response
    {
        $movies = $category->getMovies();

        $format = $request->getPreferredFormat();

        if ($format === 'json_hal') {
            // return error message if no movies found
                $data = ['message' => 'Aucun film trouvé.'];
                $response = new Response($this->serializer->serialize($data, $format));
                $response->headers->set('Content-Type', $request->getMimeType($format));
                return $response;

                // TODO a faire le reste

            /*$adapter = new ArrayAdapter($movies->toArray());
            $pagerfanta = new Pagerfanta($adapter);
            $representation = $pagerfantaFactory->createRepresentation(
                $pagerfanta,
                new \Hateoas\Configuration\Route('category_movies', ['id' => $category->getId()], true)
            );

            $response = new Response($this->serializer->serialize($representation, $format));*/
        } else {
            $data = $this->serializer->normalize($movies, null, [
                AbstractNormalizer::ATTRIBUTES => ['id', 'title', 'description', 'releaseDate', 'note', 'isUnderEightTeen', 'category'],
            ]);

            $response = new Response($this->serializer->serialize($data, $format));
        }

        $response->headers->set('Content-Type', $request->getMimeType($format));

        return $response;
    }

    #[Route('/{id}/movies', name: 'category_add_movie', methods: ['POST'])]
    public function addMovie(Request $request, Category $category): Response
    {
        $format = $request->getPreferredFormat();

        if (!in_array($format, [JsonEncoder::FORMAT, XmlEncoder::FORMAT])) {
            $format = JsonEncoder::FORMAT; // default to JSON
        }

        $data = json_decode($request->getContent(), true);

        $movie = $data['movie'];

        $category->addMovie($movie);

        $this->entityManager->flush();

        $format = $request->headers->get('Accept', 'application/json');

        $data = ['message' => 'Film ajouté avec succès.'];

        $response = new Response($this->serializer->serialize($data, $format));
        $response->headers->set('Content-Type', $request->getMimeType($format));

        return $response;
    }


}
