<?php

namespace App\Controller;

use App\Entity\Adresses;
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
    public function index(Request $request): Response
    {
        $form = $this->createForm(PersonnesAdressesType::class, null);
        $formModifier = $this->createForm(PersonnesAdressesType::class, null);
        if($request->isXmlHttpRequest()){
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


            //obj de type DATETIME
            $naissance = \DateTime::createFromFormat("d/m/Y", $data["naissance"]);
            $data["naissance"] = $naissance;

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
                $data["adresse"] = $newAdresse;

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
}
