<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\JsonConverter;
use App\Entity\Publication;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

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

    #[Route('/api/publications', methods: ['POST'])]
    #[OA\Post(description: 'Crée une nouvelle publication et retourne ses informations.')]
    #[OA\Response(
        response: 200,
        description: 'La publication créée',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'number'),
                new OA\Property(property: 'photo', type: 'string'),
                new OA\Property(property: 'datePublication', type: 'datetime'),
                new OA\Property(property: 'population', type: 'integer')
            ]
        )
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'nom', type: 'string'),
                new OA\Property(property: 'superficie', type: 'number'),
                new OA\Property(property: 'population', type: 'integer')
            ]
        )
    )]
    #[OA\Tag(name: 'Publications')]
    public function createPublication()
    {

    }

}