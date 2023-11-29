<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\JsonConverter;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\RatingCommentaire;
use App\Entity\Commentaire;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

class RatingComController extends AbstractController
{
    private $jsonConverter;
    public function __construct(JsonConverter $jsonConverter)
    {
        $this->jsonConverter = $jsonConverter;
    }
    #[Route('/api/ratingCommentaire', methods: ['GET'])]
    #[OA\Get(description: "Retourne la liste de tous les likes et dislikes des commentaires")]
    #[OA\Response(
        response: 200,
        description: "Une liste de likes et dislikes",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: RatingCommentaire::class))
        )
    )]
    #[OA\Tag(name: 'Likes et dislikes commentaires')]
    public function getRatingCommentaires(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();

        $ratingCommentaires = $entityManager->getRepository(RatingCommentaire::class)->findAll();

        return new Response($this->jsonConverter->encodeToJson($ratingCommentaires));
    }

    #[Route('/api/ratingCommentaire', methods: ['POST'])]
    #[OA\Post(description: 'Met un like/dislike sur un commentaire et retourne ses informations.')]
    #[OA\Response(
        response: 200,
        description: 'Le like/dislike créée',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'string'),
                new OA\Property(property: 'commentaire_id', type: 'number'),
                new OA\Property(property: 'likes_count', type: 'number'),
                new OA\Property(property: 'dislikes_count', type: 'number')
            ]
        )
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'string'),
                new OA\Property(property: 'commentaire_id', type: 'number'),
                new OA\Property(property: 'action', type: 'string')
            ]
        )
    )]
    #[OA\Tag(name: 'Likes et dislikes commentaires')]
    public function createRating(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        $commentaire = $doctrine->getRepository(RatingCommentaire::class)->find($data['commentaire_id']);
        $commentaire = $doctrine->getRepository(RatingCommentaire::class)->find($data['id']);

        if (!$commentaire) {
            return new Response('Commentaire non trouvé', 404);
        }


        if ($data['action'] === 'like') {
            $commentaire->setLikesCount($commentaire->getLikesCount() + 1);
        } elseif ($data['action'] === 'dislike') {
            $commentaire->setDislikesCount($commentaire->getDislikesCount() + 1);
        } else {
            return new Response('Action non valide', 400);
        }

        $entityManager->persist($commentaire);
        $entityManager->flush();
        return new Response($this->jsonConverter->encodeToJson($commentaire));
    }

}