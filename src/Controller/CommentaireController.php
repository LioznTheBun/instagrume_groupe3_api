<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\JsonConverter;
use App\Entity\Commentaire;
use App\Entity\User;
use App\Entity\Publication;
use App\Entity\RatingCommentaire;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

class CommentaireController extends AbstractController
{
    private $jsonConverter;
    public function __construct(JsonConverter $jsonConverter)
    {
        $this->jsonConverter = $jsonConverter;
    }
    #[Route('/api/commentaires', methods: ['GET'])]
    #[OA\Get(description: "Retourne la liste de tous les commentaires")]
    #[OA\Response(
        response: 200,
        description: "Une liste de commentaires",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Commentaire::class))
        )
    )]
    #[OA\Tag(name: 'Commentaires')]
    public function getCommentaires(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();

        $commentaires = $entityManager->getRepository(Commentaire::class)->findAll();

        return new Response($this->jsonConverter->encodeToJson($commentaires));
    }

    #[Route('/api/commentaires', methods: ['POST'])]
    #[OA\Post(description: 'Crée un nouveau commentaire et retourne ses informations.')]
    #[OA\Response(
        response: 200,
        description: 'Le commentaire crée',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'contenu', type: 'string', default: 'votre commentaire'),
                new OA\Property(property: 'dateComm', type: 'datetime', default: ''),
                new OA\Property(property: 'auteur_id', type: 'number'),
                new OA\Property(property: 'publication', type: 'number'),
                new OA\Property(property: 'parentCommentId', type: 'number')
            ]
        )
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'contenu', type: 'string', default: 'votre commentaire'),
                new OA\Property(property: 'dateComm', type: 'datetime', default: ''),
                new OA\Property(property: 'auteur_id', type: 'number'),
                new OA\Property(property: 'publication', type: 'number'),
                new OA\Property(property: 'parentCommentId', type: 'number')
            ]
        )
    )]
    #[OA\Tag(name: 'Commentaires')]
    public function createCommentaire(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        $commentaire = new Commentaire();
        $commentaire->setContenu($data['contenu']);
        $commentaire->setDateComm(new \DateTime($data['dateComm']));

        $userRepo = $entityManager->getRepository(User::class)->find($data['auteur_id']);
        $commentaire->setAuteur($userRepo);

        $publicationRepo = $entityManager->getRepository(Publication::class)->find($data['publication']);
        $commentaire->setPublication($publicationRepo);
        
        $commentaire->setParentCommentId($data['parentCommentId']);

        $ratingCommentaire = new RatingCommentaire();
        $ratingCommentaire->setLikesCount(0);
        $ratingCommentaire->setDislikesCount(0);
        $ratingCommentaire->setCommentaire($commentaire);

        $entityManager->persist($commentaire);
        $entityManager->persist($ratingCommentaire);
        $entityManager->flush();

        return new Response($this->jsonConverter->encodeToJson($commentaire));
    }

    #[Route('/api/editComment', methods: ['POST'])]
    #[OA\Post(description: "Modifie le contenu d'un commentaire et retourne ses informations")]
    #[OA\Response(
        response: 200,
        description: 'Le commentaire mis à jour',
        content: new OA\JsonContent(ref: new Model(type: Commentaire::class))
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'string'),
                new OA\Property(property: 'contenu', type: 'string')
            ]
        )
    )]
    #[OA\Tag(name: 'Commentaires')]
    public function updateCommentaire(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        $commentaire = $doctrine->getRepository(Commentaire::class)->find($data['id']);

        if (!$commentaire) {
            throw $this->createNotFoundException(
                'Pas de commentaire'
            );
        }

        $commentaire->setContenu($data['contenu']);

        $entityManager->persist($commentaire);
        $entityManager->flush();

        return new Response($this->jsonConverter->encodeToJson($commentaire));
    }

    #[Route('/api/commentaires/{id}', methods: ['DELETE'])]
    #[OA\Delete(description: "Supprime un commentaire correspondant à un identifiant")]
    #[OA\Response(
        response: 200,
        description: 'Le commentaire supprimé',
        content: new OA\JsonContent(ref: new Model(type: Commentaire::class))
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        schema: new OA\Schema(type: 'integer'),
        required: true,
        description: 'L\'identifiant d\'un commentaire'
    )]
    #[OA\Tag(name: 'Commentaires')]
    public function deleteCommentaire(ManagerRegistry $doctrine, $id)
    {
        $entityManager = $doctrine->getManager();

        $commentaire = $entityManager->getRepository(Commentaire::class)->find($id);

        $entityManager->remove($commentaire);
        $entityManager->flush();

        return new Response($this->jsonConverter->encodeToJson($commentaire));
    }



}