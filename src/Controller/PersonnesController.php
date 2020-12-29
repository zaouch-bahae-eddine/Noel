<?php

namespace App\Controller;

use App\Entity\Personnes;
use App\Form\PersonnesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonnesController extends AbstractController
{
    /**
     * @Route("/personnes", name="personnes")
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(PersonnesType::class, null);
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
            return $this->render('personnes/index.html.twig', ['personnesFormAjouter' => $form->createView()]);
        }

    }
}
