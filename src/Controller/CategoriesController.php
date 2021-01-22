<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Form\CategroriesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoriesController extends AbstractController
{
    /**
     * @Route("/categories", name="categories", methods={"GET"})
     */
    public function index(Request $request)
    {
        $form = $this->createForm(CategroriesType::class);
        $formModifier = $this->createForm(CategroriesType::class);
        if( $request->isXmlHttpRequest()){
            $repo = $this->getDoctrine()->getRepository(Categories::class);
            $tabData = $repo->findAll();
            $dataJson = [];
            $i = 0;
            foreach ($tabData as $categorie) {
                $ligne = [
                    "id" => $categorie->getId(),
                    "nom" => $categorie->getNom(),
                ];

                $somme = 0;
                $moyenne = 0;
                foreach ($categorie->getCadeaux() as $cadeau) {
                    $somme = +$cadeau->getPrix();
                }
                if (count($categorie->getCadeaux()) != 0){
                    $moyenne = $somme / count($categorie->getCadeaux());
                }
                $ligne["moyenne"] = $moyenne;
                $dataJson[$i] = $ligne;
                $i++;
            }

            return new JsonResponse($dataJson);
        } else {
            return $this->render('Categories/index.html.twig', [
                "categoriesFormAjouter" => $form->createView(),
                "categoriesFormModifier" => $formModifier->createView(),
            ]);
        }

    }
    /**
     * @Route("/categories/modifier/{id}", name="modifier_categories", methods={"POST"})
     */
    public function modifierCategorie(Categories $categorie, Request $request, ValidatorInterface $validator)
    {

        if($categorie != null) {
            $data = $this->getJson($request);
            $categorie->setNom($data["nom"]);
            if(count($validator->validate($categorie)) > 0){
                return new JsonResponse(["fail" => 'donneés invalid'], 422);
            }
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return new JsonResponse(["success" => $categorie->getId()],200);
        }
    }
    /**
     * @Route("/categories/supprimer/{id}", name="supprimer_categories", methods={"DELETE"})
     */
    public function supprimerCategorie(Categories $categorie, Request $request, ValidatorInterface $validator)
    {
        if($categorie != null){
            $cadeaux = $categorie->getCadeaux();
            if($cadeaux->count() == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($categorie);
                $em->flush();
                return new JsonResponse(["success" => "Categorie supprimeés"],200);
            } else {
                return new JsonResponse(["fail" => "Categorie contienne un ou plusieurs cadeaux"], 422);
            }
        }
    }
    /**
     * @param Request $request
     * @return mixed
     */
    private function getJson(Request $request)
    {

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpException(400, 'Invalid json');
        }
        return $data;
    }
}
