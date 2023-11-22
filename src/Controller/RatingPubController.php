<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\JsonConverter;
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

}