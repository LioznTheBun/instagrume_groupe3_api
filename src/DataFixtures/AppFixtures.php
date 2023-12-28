<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Entity\Commentaire;
use App\Entity\Publication;
use App\Entity\RatingCommentaire;
use App\Entity\RatingPublication;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        //création de users
        $userAdmin = new User();
        $userAdmin->setPseudo('admin');
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setAvatar("default.png");
        $userAdmin->setEmail("admin@admin.fr");
        $userAdmin->setIsBanned(false);
        $userAdmin->setPassword($this->passwordHasher->hashPassword($userAdmin, 'password'));
        $manager->persist($userAdmin);

        $utilisateur1 = new User();
        $utilisateur1->setPseudo('Quentin');
        $utilisateur1->setRoles(["ROLE_USER"]);
        $utilisateur1->setAvatar("image1.png");
        $utilisateur1->setEmail("quentin@quentin.fr");
        $utilisateur1->setIsBanned(false);
        $utilisateur1->setPassword($this->passwordHasher->hashPassword($utilisateur1, 'password'));
        $manager->persist($utilisateur1);

        $utilisateur2 = new User();
        $utilisateur2->setPseudo('Hugo');
        $utilisateur2->setRoles(["ROLE_USER"]);
        $utilisateur2->setAvatar("image2.png");
        $utilisateur2->setEmail("hugo@hugo.fr");
        $utilisateur2->setIsBanned(false);
        $utilisateur2->setPassword($this->passwordHasher->hashPassword($utilisateur2, 'password'));
        $manager->persist($utilisateur2);

        $utilisateur3 = new User();
        $utilisateur3->setPseudo('Baptiste');
        $utilisateur3->setRoles(["ROLE_USER"]);
        $utilisateur3->setAvatar("image3.png");
        $utilisateur3->setEmail("baptiste@baptiste.fr");
        $utilisateur3->setIsBanned(false);
        $utilisateur3->setPassword($this->passwordHasher->hashPassword($utilisateur3, 'password'));
        $manager->persist($utilisateur3);

        //création des publications
        $publication1 = new Publication();
        $publication1->setAuteur($utilisateur2);
        $publication1->setDatePublication(new \DateTime('2023-11-15 07:33:40'));
        $publication1->setDescription('Enfin des fruits de saison !');
        $publication1->setIsLocked(false);
        $publication1->setPhoto('image_post1.png');
        $manager->persist($publication1);

        $publication2 = new Publication();
        $publication2->setAuteur($utilisateur1);
        $publication2->setDatePublication(new \DateTime('2023-11-18 14:25:40'));
        $publication2->setDescription("Ce matin j'ai récolté de belles mangues, regardez !");
        $publication2->setIsLocked(false);
        $publication2->setPhoto('image_post2.png');
        $manager->persist($publication2);

        $publication3 = new Publication();
        $publication3->setAuteur($utilisateur2);
        $publication3->setDatePublication(new \DateTime('2023-12-01 17:02:1'));
        $publication3->setDescription("Une très belle pomme, elle attend juste d'être ramassée !");
        $publication3->setIsLocked(false);
        $publication3->setPhoto('image_post3.png');
        $manager->persist($publication3);

        //création des commentaires
        $commentaire1 = new Commentaire();
        $commentaire1->setContenu("Très belle photo");
        $commentaire1->setDateComm(new \DateTime('2023-11-15 08:33:40'));
        $commentaire1->setAuteur($utilisateur1);
        $commentaire1->setPublication($publication1);
        $manager->persist($commentaire1);

        $commentaire2 = new Commentaire();
        $commentaire2->setContenu("Les miennes sont plus jolies");
        $commentaire2->setDateComm(new \DateTime('2023-11-15 11:33:40'));
        $commentaire2->setAuteur($utilisateur2);
        $commentaire2->setPublication($publication2);
        $manager->persist($commentaire2);

        $commentaire3 = new Commentaire();
        $commentaire3->setContenu("Menteur! Elles ne sont pas fraiches");
        $commentaire3->setDateComm(new \DateTime('2023-12-02 10:33:40'));
        $commentaire3->setAuteur($utilisateur3);
        $commentaire3->setPublication($publication3);
        $manager->persist($commentaire3);

        $reponseCommentaire1 = new Commentaire();
        $reponseCommentaire1->setContenu("Non elle n'est pas si belle");
        $reponseCommentaire1->setDateComm(new \DateTime('2023-11-15 09:33:40'));
        $reponseCommentaire1->setAuteur($utilisateur3);
        $reponseCommentaire1->setPublication($publication1);
        $reponseCommentaire1->setParentCommentId(1);
        $manager->persist($reponseCommentaire1);

        $reponseCommentaire2 = new Commentaire();
        $reponseCommentaire2->setContenu("Bof en vrai");
        $reponseCommentaire2->setDateComm(new \DateTime('2023-11-15 09:33:40'));
        $reponseCommentaire2->setAuteur($utilisateur3);
        $reponseCommentaire2->setPublication($publication2);
        $reponseCommentaire2->setParentCommentId(2);
        $manager->persist($reponseCommentaire2);

        $reponseCommentaire3 = new Commentaire();
        $reponseCommentaire3->setContenu("Oui magnifique");
        $reponseCommentaire3->setDateComm(new \DateTime('2023-11-15 09:33:40'));
        $reponseCommentaire3->setAuteur($utilisateur2);
        $reponseCommentaire3->setPublication($publication3);
        $reponseCommentaire3->setParentCommentId(3);
        $manager->persist($reponseCommentaire3);

        //création rating commentaires
        $ratingCommentaire1 = new RatingCommentaire();
        $ratingCommentaire1->setCommentaire($commentaire1);
        $ratingCommentaire1->setLikesCount(1);
        $ratingCommentaire1->setDislikesCount(0);
        $manager->persist($ratingCommentaire1);

        $ratingCommentaire2 = new RatingCommentaire();
        $ratingCommentaire2->setCommentaire($commentaire2);
        $ratingCommentaire2->setLikesCount(1);
        $ratingCommentaire2->setDislikesCount(0);
        $manager->persist($ratingCommentaire2);

        $ratingCommentaire3 = new RatingCommentaire();
        $ratingCommentaire3->setCommentaire($commentaire3);
        $ratingCommentaire3->setLikesCount(0);
        $ratingCommentaire3->setDislikesCount(0);
        $manager->persist($ratingCommentaire3);

        //création rating publications
        $ratingPublication1 = new RatingPublication();
        $ratingPublication1->setPublication($publication1);
        $ratingPublication1->setLikesCount(1);
        $ratingPublication1->setDislikesCount(0);
        $manager->persist($ratingPublication1);

        $ratingPublication2 = new RatingPublication();
        $ratingPublication2->setPublication($publication2);
        $ratingPublication2->setLikesCount(5);
        $ratingPublication2->setDislikesCount(12);
        $manager->persist($ratingPublication2);

        $ratingPublication3 = new RatingPublication();
        $ratingPublication3->setPublication($publication3);
        $ratingPublication3->setLikesCount(5);
        $ratingPublication3->setDislikesCount(12);
        $manager->persist($ratingPublication3);


        $manager->flush();
    }
}
