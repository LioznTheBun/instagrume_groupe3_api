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
                new OA\Property(property: 'photo', type: 'string'),
                new OA\Property(property: 'datePublication', type: 'datetime'),
                new OA\Property(property: 'description', type: 'string'),
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
                new OA\Property(property: 'description', type: 'string'),
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
        $publication->setPhoto($data['photo']);

        $ratingPublication = new RatingPublication();
        $ratingPublication->setLikesCount(0);
        $ratingPublication->setDislikesCount(0);
        $ratingPublication->setPublication($publication);


        $entityManager->persist($publication);
        $entityManager->persist($ratingPublication);
        $entityManager->flush();

        return new Response($this->jsonConverter->encodeToJson($publication));
    }

}