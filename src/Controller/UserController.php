<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use OpenApi\Attributes as OA;

use App\Service\JsonConverter;
use App\Entity\User;
use App\Entity\Commentaire;
use App\Entity\Publication;
use App\Entity\RatingCommentaire;
use App\Entity\RatingPublication;

class UserController extends AbstractController
{

    private $jsonConverter;
    private $passwordHasher;

    public function __construct(JsonConverter $jsonConverter, UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->jsonConverter = $jsonConverter;
    }

    #[Route('/api/login', methods: ['POST'])]
    #[Security(name: null)]
    #[OA\Post(description: 'Connexion à l\'API')]
    #[OA\Response(
        response: 200,
        description: 'Un token'
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'email', type: 'string', default: 'admin@admin.fr'),
                new OA\Property(property: 'password', type: 'string', default: 'password')
            ]
        )
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    public function logUser(ManagerRegistry $doctrine, JWTTokenManagerInterface $JWTManager)
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if (!$user) {
            return new Response($this->jsonConverter->encodeToJson("Identifiants Invalides."));
        }
        if (!$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new Response($this->jsonConverter->encodeToJson("Identifiants Invalides."));
        }

        $token = $JWTManager->create($user);
        return new JsonResponse(['token' => $token]);
    }

    #[Route('/api/myself', methods: ['GET'])]
    #[OA\Get(description: 'Retourne l\'utilisateur authentifié')]
    #[OA\Response(
        response: 200,
        description: 'L\'utilisateur correspondant au token passé dans le header',
        content: new OA\JsonContent(ref: new Model(type: User::class))
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    public function getUtilisateur(ManagerRegistry $doctrine, JWTEncoderInterface $jwtEncoder, Request $request)
    {
        $entityManager = $doctrine->getManager();
        $tokenString = str_replace('Bearer ', '', $request->headers->get('Authorization'));

        $user = $jwtEncoder->decode($tokenString);
        $email = $user['username'];
        $userData = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        return new Response($this->jsonConverter->encodeToJson($userData));
    }

    #[Route('/api/users', methods: ['GET'])]
    #[OA\Get(description: 'Retourne la liste de tous les utilisateurs')]
    #[OA\Response(
        response: 200,
        description: 'La liste de tous les utilisateurs',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class))
        )
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    public function getAllUsers(ManagerRegistry $doctrine)
    {

        $entityManager = $doctrine->getManager();

        $users = $entityManager->getRepository(User::class)->findAll();
        return new Response($this->jsonConverter->encodeToJson($users));
    }

    #[Route('/api/utilisateurs/{pseudo}', methods: ['GET'])]
    #[OA\Get(description: 'Retourne les informations d\'un utilisateur correspondant à un pseudo')]
    #[OA\Response(
        response: 200,
        description: 'Les informations d\'un utilisateur',
        content: new OA\JsonContent(ref: new Model(type: User::class))
    )]
    #[OA\Parameter(
        name: 'pseudo',
        in: 'path',
        schema: new OA\Schema(type: 'string'),
        required: true,
        description: 'Le pseudo d\'un utilisateur'
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    public function getUserByPseudo(ManagerRegistry $doctrine, $pseudo)
    {
        $entityManager = $doctrine->getManager();

        $user = $entityManager->getRepository(User::class)->findOneBy(['pseudo' => $pseudo]);
        $entityManager->flush();

        return new Response($this->jsonConverter->encodeToJson($user));
    }

    #[Route('/api/inscription', methods: ['POST'])]
    #[OA\Post(description: 'Crée un nouvel utilisateur et retourne ses informations')]
    #[OA\Response(
        response: 200,
        description: 'Le nouvel utilisateur crée',
        content: new OA\JsonContent(ref: new Model(type: User::class))
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'email', type: 'string', default: 'test@gmail'),
                new OA\Property(property: 'password', type: 'string', default: 'password'),
                new OA\Property(property: 'avatar', type: 'string', default: ''),
                new OA\Property(property: 'pseudo', type: 'string', default: 'Test'),
            ]
        )
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    public function createUser(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        if ($doctrine->getRepository(User::class)->findOneBy(['email' => $data['email']])) {

            return new Response($this->jsonConverter->encodeToJson("Cet email est déjà utilisé."));
        } elseif ($doctrine->getRepository(User::class)->findOneBy(['pseudo' => $data['pseudo']])) {

            return new Response($this->jsonConverter->encodeToJson("Ce pseudo est déjà pris."));
        } else {
            $user = new User();
            $user->setEmail($data['email']);
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
            $user->setPseudo($data['pseudo']);
            $user->setIsBanned(false);
            $user->setRoles(["ROLE_USER"]);

            $imageBase64 = $data['avatar'];
            $image = base64_decode($imageBase64);
            $imageName = uniqid() . '.png';
            file_put_contents(__DIR__ . '/../../public/img/' . $imageName, $image);

            $user->setAvatar($imageName);

            $entityManager->persist($user);
            $entityManager->flush();

            return new Response($this->jsonConverter->encodeToJson("Le compte a été crée."));
        }
    }

    #[Route('/api/changePass', methods: ['PUT'])]
    #[OA\Put(description: "Modifie son mot de passe et retourne ses informations")]
    #[OA\Response(
        response: 200,
        description: 'Le mot de passe mis à jour',
        content: new OA\JsonContent(ref: new Model(type: User::class))
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'string'),
                new OA\Property(property: 'password', type: 'string')
            ]
        )
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    public function updatePassword(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        $user = $doctrine->getRepository(User::class)->find($data['id']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        if (!$user) {
            return new Response('Utilisateur non trouvé', 404);
        }


        $entityManager->persist($user);
        $entityManager->flush();

        return new Response($this->jsonConverter->encodeToJson($user));
    }

    #[Route('/api/changeAvatar', methods: ['PUT'])]
    #[OA\Put(description: "Modifie sa photo de profil et retourne ses informations")]
    #[OA\Response(
        response: 200,
        description: "L'avatar mis à jour",
        content: new OA\JsonContent(ref: new Model(type: User::class))
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'string'),
                new OA\Property(property: 'avatar', type: 'string')
            ]
        )
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    public function updateAvatar(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        $user = $doctrine->getRepository(User::class)->find($data['id']);

        $imageBase64 = $data['avatar'];
        $image = base64_decode($imageBase64);
        $imageName = uniqid() . '.png';
        file_put_contents(__DIR__ . '/../../public/img/' . $imageName, $image);

        $user->setAvatar($imageName);

        $entityManager->persist($user);
        $entityManager->flush();

        return new Response($this->jsonConverter->encodeToJson($user));
    }

    #[Route('/api/user/{id}', methods: ['GET'])]
    #[OA\Get(description: 'Retourne toutes les informations d\'un utilisateur')]
    #[OA\Response(
        response: 200,
        description: 'Toutes les informations d\'un utilisateur',
        content: new OA\JsonContent(ref: new Model(type: User::class))
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        schema: new OA\Schema(type: 'string'),
        required: true,
        description: 'L\'identifiant de l\'utilisateur'
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    public function getUserDetails(ManagerRegistry $doctrine, $id)
    {
        $entityManager = $doctrine->getManager();

        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new Response($this->jsonConverter->encodeToJson("Utilisateur non trouvé"), 404);
        }

        $userData = [
            'email' => $user->getEmail(),
            'avatar' => $user->getAvatar(),
            'pseudo' => $user->getPseudo(),
            'roles' => $user->getRoles(),
            'is_banned' => $user->isIsBanned(),
            'id' => $user->getId()
        ];

        return new Response($this->jsonConverter->encodeToJson($userData));
    }

    #[Route('/api/user/{id}/details', methods: ['GET'])]
    #[OA\Get(description: 'Retourne toutes les informations d\'un utilisateur avec ses publications et commentaires')]
    #[OA\Response(
        response: 200,
        description: 'Toutes les informations de l\'utilisateur avec publications et commentaires',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'user', ref: new Model(type: User::class)),
                new OA\Property(property: 'publications', type: 'array', items: new OA\Items(ref: new Model(type: Publication::class))),
                new OA\Property(property: 'commentaires', type: 'array', items: new OA\Items(ref: new Model(type: Commentaire::class)))
            ]
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        schema: new OA\Schema(type: 'integer'),
        required: true,
        description: 'L\'identifiant de l\'utilisateur'
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    public function getUserDetailsWithPublicationsAndCommentaires(ManagerRegistry $doctrine, $id)
    {
        $entityManager = $doctrine->getManager();

        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new Response($this->jsonConverter->encodeToJson("Utilisateur non trouvé"), 404);
        }

        $userData = [
            'user' => $user,
            'publications' => $user->getPublications(),
            'commentaires' => $user->getCommentaires()
        ];

        return new Response($this->jsonConverter->encodeToJson($userData));
    }


    #[Route('/api/ban/{userId}', methods: ['PUT'])]
    #[OA\Get(description: "Ban un utilisateur.")]
    #[OA\Response(
        response: 200,
        description: 'L\'utilisateur est ban.',
        content: new OA\JsonContent(ref: new Model(type: User::class))
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        schema: new OA\Schema(type: 'integer'),
        required: true,
        description: 'L\'identifiant d\'un utilisateur'
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    public function banUser($userId, ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $user = $doctrine->getRepository(User::class)->find($userId);
        $user->setIsBanned(1);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['status' => 'success']);
    }

    #[Route('/api/unban/{userId}', methods: ['PUT'])]
    #[OA\Get(description: "Retire le ban d'un utilisateur.")]
    #[OA\Response(
        response: 200,
        description: 'L\'utilisateur est déban.',
        content: new OA\JsonContent(ref: new Model(type: User::class))
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        schema: new OA\Schema(type: 'integer'),
        required: true,
        description: 'L\'identifiant d\'un utilisateur'
    )]
    #[OA\Tag(name: 'Utilisateurs')]
    public function unbanUser($userId, ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $user = $doctrine->getRepository(User::class)->find($userId);
        $user->setIsBanned(0);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['status' => 'success']);
    }
}
