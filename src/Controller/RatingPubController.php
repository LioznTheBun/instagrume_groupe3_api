<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\JsonConverter;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\RatingPublication;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

class RatingPubController extends AbstractController
{
    private $jsonConverter;
    public function __construct(JsonConverter $jsonConverter)
    {
        $this->jsonConverter = $jsonConverter;
    }
    #[Route('/api/ratingPublication', methods: ['GET'])]
    #[OA\Get(description: "Retourne la liste de tous les likes et dislikes des publications")]
    #[OA\Response(
        response: 200,
        description: "Une liste de likes et dislikes",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: RatingPublication::class))
        )
    )]
    #[OA\Tag(name: 'Likes et dislikes publications')]
    public function getRatingPublications(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();

        $ratingPublications = $entityManager->getRepository(RatingPublication::class)->findAll();

        return new Response($this->jsonConverter->encodeToJson($ratingPublications));
    }

    //fqfzzfqfqzizfqoi

    #[Route('/api/ratingPublication', methods: ['POST'])]
    #[OA\Post(description: 'Met un like/dislike sur une publication et retourne ses informations.')]
    #[OA\Response(
        response: 200,
        description: 'Le like/dislike créée',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'string'),
                new OA\Property(property: 'publication_id', type: 'number'),
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
                new OA\Property(property: 'publication_id', type: 'number'),
                new OA\Property(property: 'action', type: 'string')
            ]
        )
    )]
    #[OA\Tag(name: 'Likes et dislikes publications')]
    public function createRating(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        $publication = $doctrine->getRepository(RatingPublication::class)->find($data['publication_id']);
        $publication = $doctrine->getRepository(RatingPublication::class)->find($data['id']);

        if ($data['action'] === 'like') {
            $publication->setLikesCount($publication->getLikesCount() + 1);
        } elseif ($data['action'] === 'dislike') {
            $publication->setDislikesCount($publication->getDislikesCount() + 1);
        } else {
            return new Response('Action non valide', 400);
        }

        $entityManager->persist($publication);
        $entityManager->flush();
        return new Response($this->jsonConverter->encodeToJson($publication));
    }

}