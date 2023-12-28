<?php

namespace App\Controller;

use App\Entity\ArrayRatingPost;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\JsonConverter;
use App\Entity\User;
use App\Entity\RatingPublication;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\JsonResponse;

class ArrayRatingPostController extends AbstractController
{
	private $jsonConverter;
	public function __construct(JsonConverter $jsonConverter)
	{
		$this->jsonConverter = $jsonConverter;
	}

	#[Route('/api/arrayRatingPost', methods: ['GET'])]
	#[OA\Get(description: "Retourne la liste de tous les like/dislikes de post")]
	#[OA\Response(
		response: 200,
		description: "Une liste de likeDislike de post",
		content: new OA\JsonContent(
			type: 'array',
			items: new OA\Items(ref: new Model(type: ArrayRatingPost::class))
		)
	)]
	#[OA\Tag(name: 'ArrayRatingPost')]
	public function getArrayRatingPost(ManagerRegistry $doctrine)
	{
		$entityManager = $doctrine->getManager();

		$commentaires = $entityManager->getRepository(ArrayRatingPost::class)->findAll();

		return new Response($this->jsonConverter->encodeToJson($commentaires));
	}



	#[Route('/api/arrayRatingPost', methods: ['POST'])]
	#[OA\Post(description: 'Met un like/dislike sur une publication et retourne ses informations.')]
	#[OA\Response(
		response: 200,
		description: 'Le like/dislike créée',
		content: new OA\JsonContent(
			type: 'object',
			properties: [
				new OA\Property(property: 'publication_id', type: 'number'),
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
				new OA\Property(property: 'publication_id', type: 'number'),
				new OA\Property(property: 'user_id', type: 'number'),
				new OA\Property(property: 'action', type: 'string')
			]
		)
	)]
	#[OA\Tag(name: 'Likes et dislikes publications')]
	public function createArrayRatingPost(ManagerRegistry $doctrine)
	{
		$entityManager = $doctrine->getManager();
		$request = Request::createFromGlobals();
		$data = json_decode($request->getContent(), true);
		$arrayExisted = true;
		$arrayDeleted = false;

		$ratingPublication = $doctrine->getRepository(RatingPublication::class)->find($data['publication_id']);
		$user = $doctrine->getRepository(User::class)->find($data['user_id']);

		$array = $entityManager->getRepository(ArrayRatingPost::class)->findOneBy([
			'rating_publication_id' => $ratingPublication,
			'user_id' => $user
		]);

		if (!$array) {
			$array = new ArrayRatingPost();
			$array->setRatingPublicationId($ratingPublication);
			$array->setUserId($user);
			$arrayExisted = false;
		}

		if ($data['action'] === 'like') {
			if ($array->isLiked() == false && $arrayExisted) {
				$ratingPublication->setDislikesCount($ratingPublication->getDislikesCount() - 1);
			}
			if ($array->isLiked() == true && $arrayExisted) {
				$entityManager->remove($array);
				$arrayDeleted = true;
				$ratingPublication->setLikesCount($ratingPublication->getLikesCount() - 1);
			} else {
				$array->setLiked(true);
				$ratingPublication->setLikesCount($ratingPublication->getLikesCount() + 1);
				$entityManager->persist($array);
			}
		} elseif ($data['action'] === 'dislike') {
			if ($array->isLiked() == true && $arrayExisted) {
				$ratingPublication->setLikesCount($ratingPublication->getLikesCount() - 1);
			}
			if ($array->isLiked() == false && $arrayExisted) {
				$entityManager->remove($array);
				$arrayDeleted = true;
				$ratingPublication->setDislikesCount($ratingPublication->getDislikesCount() - 1);
			} else {
				$array->setLiked(false);
				$ratingPublication->setDislikesCount($ratingPublication->getDislikesCount() + 1);
				$entityManager->persist($array);
			}
		} else {
			return new Response('Action non valide', 400);
		}

		$entityManager->persist($ratingPublication);
		$entityManager->flush();
		if ($arrayDeleted){
			return new JsonResponse(([
				'likes_count' => $ratingPublication->getLikesCount(),
				'dislikes_count' => $ratingPublication->getDislikesCount(),
				'user_liked' => 'suppr'
			]));
		}
		return new JsonResponse(([
			'likes_count' => $ratingPublication->getLikesCount(),
			'dislikes_count' => $ratingPublication->getDislikesCount(),
			'user_liked' => $array->isLiked()
		]));
	}
}
