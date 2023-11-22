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
                new OA\Property(property: 'contenu', type: 'string'),
                new OA\Property(property: 'dateComm', type: 'datetime'),
                new OA\Property(property: 'auteur', type: 'string'),
                new OA\Property(property: 'publication', type: 'integer')
            ]
        )
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'contenu', type: 'string'),
                new OA\Property(property: 'dateComm', type: 'datetime'),
                new OA\Property(property: 'auteur', type: 'string'),
                new OA\Property(property: 'publication', type: 'integer')
            ]
        )
    )]
    #[OA\Tag(name: 'Commentaires')]
    public function createCommentaire(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        $userRepo = $entityManager->getRepository(User::class);
        $auteur = $userRepo->findOneBy(['pseudo' => $data['auteur']]);

        $publicationRepo = $entityManager->getRepository(Publication::class);
        $publication = $publicationRepo->findOneBy(['id' => $data['id']]);

        $commentaire = new Commentaire();
        $commentaire->setAuteur($auteur);
        $commentaire->setContenu($data['contenu']);
        $commentaire->setDateComm(new \DateTime($data['datePublication']));
        $commentaire->setPublication($publication);

        $ratingCommentaire = new RatingCommentaire();
        $ratingCommentaire->setLikesCount(0);
        $ratingCommentaire->setDislikesCount(0);
        $ratingCommentaire->setCommentaire($commentaire);


        $entityManager->persist($commentaire);
        $entityManager->persist($ratingCommentaire);
        $entityManager->flush();

        return new Response($this->jsonConverter->encodeToJson($commentaire));
    }


}