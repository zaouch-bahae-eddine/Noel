<?php

namespace App\DataFixtures;

use App\Entity\Adresses;
use App\Entity\Cadeaux;
use App\Entity\Categories;
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
        $admin = new Personnes();
        $adresseAdmin = new Adresses();
        $adresseAdmin->setVille("wohan")
            ->setCodePostal(154871)
            ->setNomRue("Les malheureux")
            ->setNumRue(123);

        $admin->setNom("admin")
            ->setRoles(['ROLE_PERE_NOEL'])
            ->setAdresse($adresseAdmin)
            ->setSexe("Homme")
            ->setNaissance(new \DateTime('21-10-1997'))
            ->setPassword($this->encoder->encodePassword($admin, "admin"));
        $manager->persist($adresseAdmin);
        $manager->persist($admin);

        $secretaire = new Personnes();
        $adresseSecretariat= new Adresses();
        $adresseSecretariat->setVille("Canical")
            ->setCodePostal(18000)
            ->setNomRue("rue Motte")
            ->setNumRue(57);

        $secretaire->setNom("secretaire")
            ->setRoles(['ROLE_SECRETARIAT'])
            ->setAdresse($adresseSecretariat)
            ->setSexe("Femme")
            ->setNaissance(new \DateTime('03/01/1985'))
            ->setPassword($this->encoder->encodePassword($secretaire, "secretaire"));
        $manager->persist($adresseSecretariat);
        $manager->persist($secretaire);

        $gestionnaire = new Personnes();
        $adresseGestionnaire= new Adresses();
        $adresseGestionnaire->setVille("hamondiaez")
            ->setCodePostal(75000)
            ->setNomRue("rue alcol")
            ->setNumRue(69);

        $gestionnaire->setNom("gestionnaire")
            ->setRoles(['ROLE_SECRETARIAT'])
            ->setAdresse($adresseGestionnaire)
            ->setSexe("Homme")
            ->setNaissance(new \DateTime('11/05/1966'))
            ->setPassword($this->encoder->encodePassword($secretaire, "secretaire"));
        $manager->persist($adresseGestionnaire);
        $manager->persist($gestionnaire);

        for($i = 0; $i < 20; $i++){

            $categorie = new Categories();
            $categorie->setNom('categorie-'.($i%5));

            $cadeaux = new Cadeaux();
            $cadeaux->setNom('cadeau-'.$i)
                ->setPrix($i)
                ->setAge($i+2)
                ->setCategorie($categorie);

            $manager->persist($categorie);
            $manager->persist($cadeaux);
        }
        $manager->flush();

        $cadeaux = $manager->getRepository(Cadeaux::class)->findAll();
        $k = 0;
        for($i = 0; $i < 9; $i++){
            $client = new Personnes();
            $adress = new Adresses();

            $adress->setVille("ville-".$i)
                ->setCodePostal($i.'0000')
                ->setNomRue("rue ".$i)
                ->setNumRue($i.'3');

            $client->setNom("user".$i)
                ->setRoles(['ROLE_SOUHAIT'])
                ->setAdresse($adress)
                ->setSexe("Homme")
                ->setNaissance(new \DateTime('11/05/19'.$i.'7'))
                ->setPassword($this->encoder->encodePassword($client, "client"));
            foreach ($cadeaux as $cadeau){
                $client->addCadeaux($cadeau);
                $k++;
            }

            $manager->persist($adress);
            $manager->persist($client);
       }
        for($i = 0; $i < 20; $i++){
            $client = new Personnes();
            $adress = new Adresses();
            $adress->setVille("ville-".($i+10))
                ->setCodePostal($i.'0000')
                ->setNomRue("rue ".($i+12))
                ->setNumRue($i.'8');

            $client->setNom("user".($i+10))
                ->setRoles(['ROLE_SOUHAIT'])
                ->setAdresse($adress)
                ->setSexe("Femme")
                ->setNaissance(new \DateTime('11/05/197'.($i%10)))
                ->setPassword($this->encoder->encodePassword($client, "client"));
            foreach ($cadeaux as $cadeau){
                $client->addCadeaux($cadeau);
                $k++;
            }
            $manager->persist($adress);
            $manager->persist($client);
        }
        $manager->flush();
    }

}
