<?php

namespace App\Controller;

use App\Entity\Cadeaux;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CadeauxController extends AbstractController
{
    /**
     * @Route("/cadeaux", name="cadeaux", methods={"GET"})
     */
    public function index(Request $request)
    {
        if( $request->isXmlHttpRequest()){
            $repo = $this->getDoctrine()->getRepository(Cadeaux::class);
            $tabData = $repo->findAll();
            $dataJson = [];
            $i = 0;
            foreach ($tabData as $cadeau){

                $ligne = [
                    "id" => $cadeau->getId(),
                    "nom" => $cadeau->getNom(),
                    "age" => $cadeau->getAge(),
                    "prix" => $cadeau->getPrix(),
                    "categorie" => $cadeau->getCategorie()->getNom(),
                ];
                $dataJson[$i] = $ligne;
                $i++;
            }
            return new JsonResponse($dataJson);
        } else {
            return $this->render('cadeaux/index.html.twig');
        }

    }
}
