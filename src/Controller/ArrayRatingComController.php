<?php

namespace App\Controller;

use App\Entity\ArrayRatingCom;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\JsonConverter;
use App\Entity\User;
use App\Entity\Commentaire;
use App\Entity\RatingCommentaire;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\JsonResponse;

class ArrayRatingComController extends AbstractController
{
	private $jsonConverter;
	public function __construct(JsonConverter $jsonConverter)
	{
		$this->jsonConverter = $jsonConverter;
	}

	#[Route('/api/arrayRatingCom', methods: ['GET'])]
	#[OA\Get(description: "Retourne la liste de tous les like/dislikes de Com")]
	#[OA\Response(
		response: 200,
		description: "Une liste de likeDislike de Com",
		content: new OA\JsonContent(
			type: 'array',
			items: new OA\Items(ref: new Model(type: ArrayRatingCom::class))
		)
	)]
	#[OA\Tag(name: 'ArrayRatingCom')]
	public function getArrayRatingCom(ManagerRegistry $doctrine)
	{
		$entityManager = $doctrine->getManager();

		$commentaires = $entityManager->getRepository(ArrayRatingCom::class)->findAll();

		return new Response($this->jsonConverter->encodeToJson($commentaires));
	}

	#[Route('/api/arrayRatingCom', methods: ['POST'])]
	#[OA\Post(description: 'Met un like/dislike sur une commentaire et retourne ses informations.')]
	#[OA\Response(
		response: 200,
		description: 'Le like/dislike créée',
		content: new OA\JsonContent(
			type: 'object',
			properties: [
				new OA\Property(property: 'commentaire_id', type: 'number'),
				new OA\Property(property: 'user_id', type: 'number'),
				new OA\Property(property: 'liked', type: 'boolean')
			]
		)
	)]
	#[OA\RequestBody(
		required: true,
		content: new OA\JsonContent(
			type: 'object',
			properties: [
				new OA\Property(property: 'commentaire_id', type: 'number'),
				new OA\Property(property: 'user_id', type: 'number'),
				new OA\Property(property: 'action', type: 'string')
			]
		)
	)]
	#[OA\Tag(name: 'Likes et dislikes commentaires')]
	public function createArrayRatingCom(ManagerRegistry $doctrine)
	{
		$entityManager = $doctrine->getManager();
		$request = Request::createFromGlobals();
		$data = json_decode($request->getContent(), true);
		$arrayExisted = true;
		$arrayDeleted = false;

		$ratingCommentaire = $doctrine->getRepository(RatingCommentaire::class)->find($data['commentaire_id']);
		$user = $doctrine->getRepository(User::class)->find($data['user_id']);

		$array = $entityManager->getRepository(ArrayRatingCom::class)->findOneBy([
			'rating_commentaire_id' => $ratingCommentaire,
			'user_id' => $user
		]);

		if (!$array) {
			$array = new ArrayRatingCom();
			$array->setRatingCommentaireId($ratingCommentaire);
			$array->setUserId($user);
			$arrayExisted = false;
		}

		//TODO: Erreur sur le changement de dislike à like

		if ($data['action'] === 'like') {
			if ($array->isLiked() == false && $arrayExisted) {
				$ratingCommentaire->setDislikesCount($ratingCommentaire->getDislikesCount() - 1);
			}
			if ($array->isLiked() == true && $arrayExisted) {
				$entityManager->remove($array);
				$arrayDeleted = true;
				$ratingCommentaire->setLikesCount($ratingCommentaire->getLikesCount() - 1);
			} else {
				$array->setLiked(true);
				$ratingCommentaire->setLikesCount($ratingCommentaire->getLikesCount() + 1);
				$entityManager->persist($array);
			}
		} elseif ($data['action'] === 'dislike') {
			if ($array->isLiked() == true && $arrayExisted) {
				$ratingCommentaire->setLikesCount($ratingCommentaire->getLikesCount() - 1);
			}
			if ($array->isLiked() == false && $arrayExisted) {
				$entityManager->remove($array);
				$arrayDeleted = true;
				$ratingCommentaire->setDislikesCount($ratingCommentaire->getDislikesCount() - 1);
			} else {
				$array->setLiked(false);
				$ratingCommentaire->setDislikesCount($ratingCommentaire->getDislikesCount() + 1);
				$entityManager->persist($array);
			}

		} else {
			return new Response('Action non valide', 400);
		}

		$entityManager->persist($ratingCommentaire);
		$entityManager->flush();
		if ($arrayDeleted){
			return new JsonResponse(([
				'likes_count' => $ratingCommentaire->getLikesCount(),
				'dislikes_count' => $ratingCommentaire->getDislikesCount(),
				'user_liked' => 'suppr'
			]));
		}
		return new JsonResponse(([
			'likes_count' => $ratingCommentaire->getLikesCount(),
			'dislikes_count' => $ratingCommentaire->getDislikesCount(),
			'user_liked' => $array->isLiked()
		]));
	}
}
