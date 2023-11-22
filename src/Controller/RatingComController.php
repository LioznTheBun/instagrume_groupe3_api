<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\JsonConverter;
use App\Entity\RatingCommentaire;
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

}