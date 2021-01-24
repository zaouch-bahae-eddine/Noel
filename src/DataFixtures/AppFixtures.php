<?php

namespace App\DataFixtures;

use App\Entity\Adresses;
use App\Entity\Personnes;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder) {

        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $adresse = new Adresses();
        $adresse->setNomRue("abc")
            ->setNumRue(57)
            ->setVille('jarna')
            ->setCodePostal('80800');

        $manager->persist($adresse);
            $user = new Personnes();
            $user->setNom("abdel")
                ->setSexe("Homme")
                ->setAdresse($adresse)
                ->setRoles(['ROLE_PERE_NOEL'])
                ->setNaissance(new \DateTime("21-01-1997"))
                ->setPassword($this->encoder->encodePassword($user,"noel"));
            $manager->persist($user);
            $manager->flush();
    }
}
