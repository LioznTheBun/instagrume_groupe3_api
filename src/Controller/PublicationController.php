<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\JsonConverter;
use App\Entity\Publication;
use App\Entity\User;
use App\Entity\RatingPublication;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\JsonResponse;

class PublicationController extends AbstractController
{
    private $jsonConverter;
    public function __construct(JsonConverter $jsonConverter)
    {
        $this->jsonConverter = $jsonConverter;
    }
    #[Route('/api/publications', methods: ['GET'])]
    #[OA\Get(description: "Retourne la liste de toutes les publications")]
    #[OA\Response(
        response: 200,
        description: "Une liste de publications",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Publication::class))
        )
    )]
    #[OA\Tag(name: 'Publications')]
    public function getPublications(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();

        $publications = $entityManager->getRepository(Publication::class)->findAll();

        return new Response($this->jsonConverter->encodeToJson($publications));
    }

    #[Route('/api/createPublication', methods: ['POST'])]
    #[OA\Post(description: 'Crée une nouvelle publication et retourne ses informations.')]
    #[OA\Response(
        response: 200,
        description: 'La publication créée',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'photo', type: 'string'),
                new OA\Property(property: 'datePublication', type: 'datetime', default: ''),
                new OA\Property(property: 'description', type: 'string', default: 'votre description'),
                new OA\Property(property: 'isLocked', type: 'boolean', default: false),
                new OA\Property(property: 'auteur', type: 'string')
            ]
        )
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'photo', type: 'string'),
                new OA\Property(property: 'datePublication', type: 'datetime', default: ''),
                new OA\Property(property: 'description', type: 'string', default: 'votre description'),
                new OA\Property(property: 'isLocked', type: 'boolean', default: false),
                new OA\Property(property: 'auteur', type: 'string', default: 'admin'),
            ]
        )
    )]
    #[OA\Tag(name: 'Publications')]
    public function createPublication(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        $userRepo = $entityManager->getRepository(User::class);
        $auteur = $userRepo->findOneBy(['pseudo' => $data['auteur']]);

        $publication = new Publication();
        $publication->setAuteur($auteur);
        $publication->setDescription($data['description']);
        $publication->setDatePublication(new \DateTime($data['datePublication']));
        $publication->setIsLocked($data['isLocked']);

        $imageBase64 = $data['photo'];
        $image = base64_decode($imageBase64);
        $imageName = uniqid() . '.png';
        file_put_contents(__DIR__ . '/../../public/img/' . $imageName, $image);
        $publication->setPhoto($imageName);

        $ratingPublication = new RatingPublication();
        $ratingPublication->setLikesCount(0);
        $ratingPublication->setDislikesCount(0);
        $ratingPublication->setPublication($publication);


        $entityManager->persist($publication);
        $entityManager->persist($ratingPublication);
        $entityManager->flush();

        return new Response($this->jsonConverter->encodeToJson($publication));
    }

    #[Route('/api/publications', methods: ['PUT'])]
    #[OA\Put(description: "Modifie la description d'une publication et retourne ses informations")]
    #[OA\Response(
        response: 200,
        description: 'La description mise à jour',
        content: new OA\JsonContent(ref: new Model(type: Publication::class))
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'string'),
                new OA\Property(property: 'description', type: 'string')
            ]
        )
    )]
    #[OA\Tag(name: 'Publications')]
    public function updateDescriptionPublication(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        $publication = $doctrine->getRepository(Publication::class)->find($data['id']);

        if (!$publication) {
            throw $this->createNotFoundException(
                'Pas de publication'
            );
        }

        $publication->setDescription($data['description']);

        $entityManager->persist($publication);
        $entityManager->flush();

        return new Response($this->jsonConverter->encodeToJson($publication));
    }

    #[Route('/api/publications/{id}', methods: ['DELETE'])]
    #[OA\Delete(description: "Supprime une publication correspondant à un identifiant")]
    #[OA\Response(
        response: 200,
        description: 'La publication supprimée',
        content: new OA\JsonContent(ref: new Model(type: Publication::class))
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        schema: new OA\Schema(type: 'integer'),
        required: true,
        description: 'L\'identifiant d\'une publication'
    )]
    #[OA\Tag(name: 'Publications')]
    public function deletePublication(ManagerRegistry $doctrine, $id)
    {
        $entityManager = $doctrine->getManager();

        $publication = $entityManager->getRepository(Publication::class)->find($id);

        $entityManager->remove($publication);
        $entityManager->flush();

        return new Response($this->jsonConverter->encodeToJson($publication));
    }

    #[Route('/api/publications/{id}', methods: ['GET'])]
    #[OA\Get(description: "Retourne une publication correspondant à un identifiant")]
    #[OA\Response(
        response: 200,
        description: 'La publication retournée',
        content: new OA\JsonContent(ref: new Model(type: Publication::class))
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        schema: new OA\Schema(type: 'integer'),
        required: true,
        description: 'L\'identifiant d\'une publication'
    )]
    #[OA\Tag(name: 'Publications')]
    public function getPublicationById(ManagerRegistry $doctrine, $id)
    {
        $entityManager = $doctrine->getManager();

        $publication = $entityManager->getRepository(Publication::class)->find($id);

        if (!$publication) {
            throw $this->createNotFoundException('Publication not found');
        }


        return new Response($this->jsonConverter->encodeToJson($publication));
    }

    #[Route('/api/lock/{postId}', methods: ['PUT'])]
    #[OA\Get(description: "Lock une publication.")]
    #[OA\Response(
        response: 200,
        description: 'La publication est bloqué.',
        content: new OA\JsonContent(ref: new Model(type: Publication::class))
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        schema: new OA\Schema(type: 'integer'),
        required: true,
        description: 'L\'identifiant d\'une publication'
    )]
    #[OA\Tag(name: 'Publications')]
    public function lockpost($postId, ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $post = $doctrine->getRepository(Publication::class)->find($postId);
        $post->setIsLocked(1);

        $entityManager->persist($post);
        $entityManager->flush();

        return new JsonResponse(['status' => 'success']);
    }

    #[Route('/api/unlock/{postId}', methods: ['PUT'])]
    #[OA\Get(description: "Unlock une publication.")]
    #[OA\Response(
        response: 200,
        description: 'La publication est débloqué.',
        content: new OA\JsonContent(ref: new Model(type: Publication::class))
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        schema: new OA\Schema(type: 'integer'),
        required: true,
        description: 'L\'identifiant d\'une publication'
    )]
    #[OA\Tag(name: 'Publications')]
    public function unlockpost($postId, ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $post = $doctrine->getRepository(Publication::class)->find($postId);
        $post->setIsLocked(0);

        $entityManager->persist($post);
        $entityManager->flush();

        return new JsonResponse(['status' => 'success']);
    }
}