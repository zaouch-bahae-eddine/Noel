<?php

namespace App\Controller;

use App\Entity\Cadeaux;
use App\Entity\Personnes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SouhaitController extends AbstractController
{
    /**
     * @Route("/souhait/cadeaux", name="souhait")
     */
    public function index(Request $request): Response
    {
        if($request->isXmlHttpRequest()){
            /**
             * @var Personnes $person
             */
            $person = $this->getUser();
            $cadeaux = $this->getDoctrine()->getManager()->getRepository(Cadeaux::class)->findAll();
            $tabCadeaux = [];
            $i = 0;
            foreach ($cadeaux as $cadeau){
                $tabCadeaux[$cadeau->getId()] = [
                    "idCadeau" => $cadeau->getId(),
                    "nom" => $cadeau->getNom(),
                    "age" => $cadeau->getAge(),
                    "prix" => $cadeau->getPrix(),
                    "categorie" => $cadeau->getCategorie()->getNom(),
                    "isIn" => false
                ];
                $i++;
            }
            $i = 0;
            foreach ($person->getCadeaux() as $cadeau) {
                $tabCadeaux[$cadeau->getId()]["isIn"] = true;
            }
            return $this->json($tabCadeaux);
        } else {
            return $this->render('souhait/index.html.twig', [
                'Ajouter' => 'SouhaitController',
            ]);
        }
    }
    /**
     * @Route("/souhait/ajouter/{id}", name="souhait_ajouter_cadeaux")
     */
    public function ajouterSouhait(Request $request, Cadeaux $cadeaux): Response
    {
        if($cadeaux) {
            /**
             * @var Personnes $person
             */
            $person = $this->getUser();
            $person->addCadeaux($cadeaux);
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->json(['success' => "cadeau Ajouté"]);
        } else {
            return $this->json(['fail' => "cadeau n'existe pas!"]);
        }
    }
    /**
     * @Route("/souhait/supprimer/{id}", name="souhait_supprimer_cadeaux")
     */
    public function supprimerSouhait(Request $request, Cadeaux $cadeaux): Response
    {
        if($cadeaux) {
            /**
             * @var Personnes $person
             */
            $person = $this->getUser();
            $person->removeCadeaux($cadeaux);
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->json(['success' => "cadeau supprimer"]);
        } else {
            return $this->json(['fail' => "cadeau n'existe pas!"]);
        }
    }
}
