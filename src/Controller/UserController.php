<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\FirebaseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private ValidatorInterface $validator;
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager,
                                UserRepository $userRepository,
                                ValidatorInterface     $validator,
                                SerializerInterface    $serializer)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(Request $request, UserRepository $userRepository, int $id): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Vous pouvez utiliser le $user pour obtenir les détails de l'utilisateur
        // Par exemple, $user->getFirstName(), $user->getEmail(), etc.

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /*#[Route('', name: 'app_user_new', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setEmail($data['email']);

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $data['password']
        );
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Film créé avec succès.', 'data' => $data], Response::HTTP_CREATED);
    }*/

    #[Route('', name: 'app_user_new', methods: ['POST'])]
    //#[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, FirebaseService $firebaseService): Response
    {
        // on récupère les paramètres de la requête
        $request_data = $request->request;

        //var_dump($request->request->get('username'));die();

        try {
            $userProperties = [
                'email' => $request_data->get('email'),
                'password' => $request_data->get('password')
            ];

            // Créer un utilisateur dans votre base de données locale
            $user = new User();
            $user->setFirstName($request_data->get('firstName') ?? '');
            $user->setLastName($request_data->get('lastName') ?? '');
            $user->setEmail($request_data->get('email'));
            // Définissez le mot de passe comme null ou comme un hash de base
            // car l'authentification est gérée par Firebase
            $user->setPassword(null);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $firebaseUser = $firebaseService->getAuth()->createUser($userProperties);

            return new JsonResponse(['message' => 'Utilisateur créé avec succès.'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'app_user_edit', methods: ['PUT'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
