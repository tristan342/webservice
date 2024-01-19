<?php

namespace App\Controller;

use App\Service\FirebaseService;
use Firebase\Auth\Token\Exception\InvalidToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route('/auth', name: 'app_auth')]
    public function index(): Response
    {
        return $this->render('auth/index.html.twig', [
            'controller_name' => 'AuthController',
        ]);
    }

    #[Route('/authenticate', name: 'authenticate', methods: ['POST'])]
    public function authenticate(Request $request, FirebaseService $firebaseService): Response
    {
        $credentials = $request->request->all();
        //($request_data);die();
        //$credentials = json_decode($request->getContent(), true);
        $email = $credentials['email'] ?? '';
        $password = $credentials['password'] ?? '';

        try {
            $auth = $firebaseService->getAuth();
            $signInResult = $auth->signInWithEmailAndPassword($email, $password);
            $idTokenString = $signInResult->idToken();

            return $this->json(['token' => $idTokenString]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
    }

    // déprécié
    public function login(Request $request, UserProviderInterface $userProvider, UserPasswordEncoderInterface $passwordEncoder, JWTTokenManagerInterface $JWTManager): Response
    {
        $data = json_decode($request->getContent(), true);

        try {
            $user = $userProvider->loadUserByUsername($data['username']);
        } catch (UsernameNotFoundException $e) {
            return $this->json(['error' => 'Username or Password invalid'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$passwordEncoder->isPasswordValid($user, $data['password'])) {
            return $this->json(['error' => 'Username or Password invalid'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $JWTManager->create($user);

        return $this->json(['token' => $token]);
    }

    #[Route('/authenticate', name: 'authenticate_user', methods: ['POST'])]
    public function authenticateUser(Request $request, FirebaseService $firebaseService)
    {
        $idTokenString = $request->headers->get('Authorization');

        if (!$idTokenString) {
            return $this->json([
                'error' => 'No token provided'
            ], Response::HTTP_BAD_REQUEST);
        }

    try {
        $verifiedIdToken = $firebaseService->getAuth()->verifyIdToken($idTokenString);
    } catch (InvalidToken $e) {
        // Gérer l'erreur de token invalide
    }

    $uid = $verifiedIdToken->getClaim('sub');
    // Récupérer ou créer un utilisateur dans votre base de données Symfony en utilisant $uid

    // Gérer la session ou renvoyer une réponse appropriée
        return $this->json(['success' => 'Authenticated successfully', 'uid' => $uid
        ], Response::HTTP_OK);
}
}
