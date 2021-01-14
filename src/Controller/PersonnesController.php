<?php

namespace App\Controller;

use App\Entity\Adresses;
use App\Entity\Cadeaux;
use App\Entity\Personnes;
use App\Form\AdressesType;
use App\Form\PersonnesAdressesType;
use App\Form\PersonnesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Dotenv\Exception\FormatException;
use Symfony\Component\Dotenv\Exception\FormatExceptionContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PersonnesController extends AbstractController
{
    /**
     * @Route("/personnes", name="personnes")
     */
    public function index(Request $request, $sfDemande = false): Response
    {
        $form = $this->createForm(PersonnesAdressesType::class, null);
        $formModifier = $this->createForm(PersonnesAdressesType::class, null);
        if($request->isXmlHttpRequest() || $sfDemande){
            $personnes = $this->getDoctrine()->getRepository(Personnes::class)->findAll();
            $i = 0;
            $dataJson = [];
            foreach ($personnes as $personne) {
                /**
                 * @var Personnes $personne
                 */
                $ligne = [
                    "id" => $personne->getId(),
                    "nom" => $personne->getNom(),
                    "naissance" => $personne->getNaissance()->format('d/m/Y'),
                    "sexe" => $personne->getSexe(),
                    "adresse" => [
                        "idAdresse" => $personne->getAdresse()->getId(),
                        "nomRue" => $personne->getAdresse()->getNomRue(),
                        "numRue" => $personne->getAdresse()->getNumRue(),
                        "ville" => $personne->getAdresse()->getVille(),
                        "codePostal" => $personne->getAdresse()->getCodePostal(),
                    ],
                ];
                $dataJson[$i] = $ligne;
                $i++;
            }
            return new JsonResponse($dataJson);
        } else {
            return $this->render('personnes/index.html.twig', [
                'personnesFormAjouter' => $form->createView(),
                'personnesFormModifier' => $formModifier->createView(),
            ]);
        }

    }
    /**
     * @Route("/personnes/ajouter", name="ajouter_personnes", methods={"POST"})
     */
    public function ajouterPersonne(Request $request, ValidatorInterface $validator){
        if($request->isXmlHttpRequest()) {
            $formAdress = $this->createForm(AdressesType::class, null);
            $formPersonne = $this->createForm(PersonnesType::class, null);

            $data = $this->getJson($request);
            $em = $this->getDoctrine()->getManager();
            $adresseExiste = true;

            //obj de type DATETIME
            $naissance = \DateTime::createFromFormat("d/m/Y", $data["naissance"]);

            //logique de creation de type DATETIME from string (year, month, day)
            if ($data["ville"] != "") {
                //Ajout la nouvelle adresse à la base de donnée
                $formAdress->submit($data);
                if (count($validator->validate($formAdress->getData())) > 0) {
                    throw new \Exception("formAdress not valid");
                }
                $newAdresse = $formAdress->getData();
                $em->persist($newAdresse);
                $em->flush();
                $adresseExiste = false;
                $formPersonne->submit($data);
                /**
                 * @var Personnes $newPersonne
                 */
                $newPersonne = $formPersonne->getData();
                $newPersonne->setAdresse($newAdresse)
                            ->setNaissance($naissance);
                if (count($validator->validate($newPersonne)) > 0) {
                    throw new \Exception("formPersonne not valid");
                }
                $em->persist($newPersonne);
                $em->flush();

            } else {
                $adresseExiste = $em->getRepository(Adresses::class)->find($data["adresse"]);
                $data["adresse"] = $adresseExiste;

                $formPersonne->submit($data);
                /**
                 * @var Personnes $newPersonne
                 */
                $newPersonne = $formPersonne->getData();
                $newPersonne->setAdresse($adresseExiste)
                            ->setNaissance($naissance);
                if (count($validator->validate($newPersonne)) > 0) {
                    throw new \Exception("formPersonne not valid");
                }
                $em->persist($newPersonne);
                $em->flush();
            }
            $ligne = [
                "id" => $newPersonne->getId(),
                "nom" => $newPersonne->getNom(),
                "naissance" => $newPersonne->getNaissance()->format('d/m/Y'),
                "sexe" => $newPersonne->getSexe(),
                "adresse" => [
                    "adresseExiste" => $adresseExiste,
                    "idAdresse" => $newPersonne->getAdresse()->getId(),
                    "nomRue" => $newPersonne->getAdresse()->getNomRue(),
                    "numRue" => $newPersonne->getAdresse()->getNumRue(),
                    "ville" => $newPersonne->getAdresse()->getVille(),
                    "codePostal" => $newPersonne->getAdresse()->getCodePostal(),
                ],
            ];
            $dataJson[0] = $ligne;
            return new JsonResponse($dataJson);
        } else {
            return new JsonResponse([["fail" => "requete ajax"]], 500);
        }
    }
    /**
     * @param Request $request
     * @return mixed
     */
    private function getJson(Request $request)
    {
        //$data est un tableau associatif de sjon envoyé par l'ajax
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpException(400, 'Invalid json');
        }
        return $data;
    }
    /**
     * @Route("/personnes/modifier/{id}", name="modifier_personne", methods={"POST"})
     */
    function modifierPersonnes(Request $request, Personnes $oldPersonnes,  ValidatorInterface $validator){
        if($request->isXmlHttpRequest()) {
            $formAdress = $this->createForm(AdressesType::class, null);
            $formPersonne = $this->createForm(PersonnesType::class, null);
            $adresseExiste = true;
            $data = $this->getJson($request);
            $em = $this->getDoctrine()->getManager();

            //obj de type DATETIME
            $naissance = \DateTime::createFromFormat("d/m/Y", $data["naissance"]);

            //logique de creation de type DATETIME from string (year, month, day)
            if ($data["ville"] != "") {
                //Ajout la nouvelle adresse à la base de donnée
                $formAdress->submit($data);
                $newAdresse = $formAdress->getData();
                if (count($validator->validate($newAdresse)) > 0) {
                    throw new \Exception("formAdress not valid");
                }
                $em->persist($newAdresse);
                $em->flush();
                $adresseExiste = false;
                $formPersonne->submit($data);
                /**
                 * @var Personnes $newPersonne
                 */
                $newPersonne = $formPersonne->getData();
                $oldPersonnes
                    ->setSexe($newPersonne->getSexe())
                    ->setAdresse($newAdresse)
                    ->setNaissance($naissance);
                if (count($validator->validate($oldPersonnes)) > 0) {
                    throw new \Exception("formPersonne not valid");
                }
                $em->persist($oldPersonnes);
                $em->flush();

            } else {
                $adresseExiste = $em->getRepository(Adresses::class)->find($data["adresse"]);
                $formPersonne->submit($data);
                /**
                 * @var Personnes $newPersonne
                 */
                $newPersonne = $formPersonne->getData();
                $oldPersonnes
                    ->setSexe($newPersonne->getSexe())
                    ->setAdresse($adresseExiste)
                    ->setNaissance($naissance);
                if (count($validator->validate($oldPersonnes)) > 0) {
                    throw new \Exception("formPersonne not valid");
                }
                $em->persist($oldPersonnes);
                $em->flush();
            }
            $ligne = [
                "id" => $oldPersonnes->getId(),
                "naissance" => $oldPersonnes->getNaissance()->format('d/m/Y'),
                "sexe" => $oldPersonnes->getSexe(),
                "adresse" => [
                    "adresseExiste" => $adresseExiste,
                    "idAdresse" => $oldPersonnes->getAdresse()->getId(),
                    "nomRue" => $oldPersonnes->getAdresse()->getNomRue(),
                    "numRue" => $oldPersonnes->getAdresse()->getNumRue(),
                    "ville" => $oldPersonnes->getAdresse()->getVille(),
                    "codePostal" => $oldPersonnes->getAdresse()->getCodePostal(),
                ],
            ];
            $dataJson[0] = $ligne;
            return new JsonResponse($dataJson);
        } else {
            return new JsonResponse([["fail" => "requete ajax"]], 500);
        }
    }

    /**
     * @Route("/personnes/supprimer/{id}", name="supprimer_personne", methods={"DELETE"})
     */
    function supprimerPersonnes(Request $request, Personnes $personnes) {

        if($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $id = $personnes->getId();
            $em->remove($personnes);
            $em->flush();
            return new JsonResponse([['success'=> $id]]);
        }
        else {
            return new JsonResponse([["fail"=> "ma requette n'est pas ajax"]]);
        }
    }
    /**
     * @Route("/personnes/filtre/ville/{id}", name="ville_filtre_personne", methods={"GET"})
     */
    function villeFiltrePersonnes(Request $request, Adresses $adresses = null, $rueFiltre = false, $sfDemande = false) {
        if ($request->isXmlHttpRequest() || $sfDemande) {
            if ($adresses == null) {
                return $this->index($request, true);
            }
            /**
             * @var Adresses $rues
             */
            if ($rueFiltre && $request->query->get("valueSelect") != 0) {
                $adresses = $this->getDoctrine()->getRepository(Adresses::class)->findAdressesByVilleAndRue($adresses->getVille(), $request->query->get("nomRue"));
            } else {
                $adresses = $this->getDoctrine()->getRepository(Adresses::class)->findAdressesByVille($adresses->getVille());
            }
            $rues = [];
            $tabRues = [];
            $personnes = [];
            $tabPersonnes = [];
            $i = 0;
            $j = 0;
            foreach ($adresses as $adresse) {
                $rue = [
                    "idAdresse" => $adresse->getId(),
                    "nomRue" => $adresse->getNomRue()
                ];
                $personnes[$i] = $this->getDoctrine()->getRepository(Personnes::class)->findBy(['adresse' => $adresse->getId()]);
                foreach ($personnes[$i] as $personne) {
                    $infoPersonne = [
                        "id" => $personne->getId(),
                        "nom" => $personne->getNom(),
                        "naissance" => $personne->getNaissance()->format('d/m/Y'),
                        "sexe" => $personne->getSexe(),
                        "adresse" => [
                            "idAdresse" => $adresse->getId(),
                            "nomRue" => $adresse->getNomRue(),
                            "numRue" => $adresse->getNumRue(),
                            "ville" => $adresse->getVille(),
                            "codePostal" => $adresse->getCodePostal()
                        ]
                    ];
                    $tabPersonnes[$j] = $infoPersonne;
                    $j++;
                }
                $tabRues[$i] = $rue;
                $i++;
            }
            $tabFiltre = ["Rues" => $tabRues, "Personnes" => $tabPersonnes];
            return new JsonResponse($tabFiltre);
        }
    }
    /**
     * @Route("/personnes/filtre/ville/{id}/rue", name="rue_filtre_personne", methods={"GET"})
     */
    function villeAndRueFiltrePersonnes(Request $request, Adresses $adresses = null, $rueFiltre = false) {
        return $this->villeFiltrePersonnes($request, $adresses, true, true);
    }
    /**
     * @Route("/personnes/{id}/cadeaux", name="cadeaux_personne", methods={"GET"})
     */
    function cadeauxDePersonnes(Request $request, Personnes $personne) {
        if($personne) {
            $cadeaux = $personne->getCadeaux();
            $tabCadeaux = [];
            $i = 0;
            foreach ($cadeaux as $cadeau) {
                $tabCadeaux[$i] = [
                    "idCadeau" => $cadeau->getId(),
                    "nom" => $cadeau->getNom(),
                    "age" => $cadeau->getAge(),
                    "prix" => $cadeau->getPrix(),
                    "categorie" =>$cadeau->getCategorie()->getNom()
                ];
                $i++;
            }
            return new JsonResponse($tabCadeaux, 200);
        } else {
            return new JsonResponse(["fail" => "personne inexistante!"], 401);
        }
    }
    /**
     * @Route("/personnes/{personne}/cadeaux/{cadeaux}", name="supprimer_cadeaux_personne", methods={"DELETE"})
     */
    function supprimerCadeauxDePersonnes(Request $request, Personnes $personne, Cadeaux $cadeaux) {
        if($personne){
            $personne->removeCadeaux($cadeaux);
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->json(["success" => "cadeau supprimée"]);
        }
        return $this->json(["fail" => "personne inexistante"]);
    }
    /**
     * @Route("/personnes/security", name="security_cadeaux_personne", methods={"GET"})
     */
    function securityCadeauxDePersonnes(Request $request)
    {
        $personnes = $this->getDoctrine()->getRepository(Personnes::class)->findAll();
        $tabPersonneSecurity = [];
        $i = 0;
        foreach ($personnes as $personne) {
            foreach ($personne->getCadeaux() as $cadeau) {
                if($personne->getAge() < $cadeau->getAge()){
                    $tabPersonneSecurity[$i] = ["id" => $personne->getId()];
                    $i++;
                    break;
                }
            }
        }
        return $this->json($tabPersonneSecurity);
    }
}
