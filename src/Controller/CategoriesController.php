<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Personnes;
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
                    $somme += $cadeau->getPrix();
                }
                if (count($categorie->getCadeaux()) != 0){
                    $moyenne = $somme / $categorie->getCadeaux()->count();
                }
                $ligne["moyenne"] = number_format($moyenne, 2);
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
     * @Route("/categories/pourcentage/{id}", name="pourcentage_categories", methods={"POST"})
     */
    public function chngerPourcentagePrixCategorie(Categories $categorie, Request $request)
    {
        $data = $this->getJson($request);
        if($categorie != null) {
            $cadeaux = $categorie->getCadeaux();
            $somme = 0;
            if ($data["type"] == "plus") {
                foreach ($cadeaux as $cadeau) {
                    $cadeau->setPrix(($cadeau->getPrix() * ($data["pourcentage"] / 100) + $cadeau->getPrix()));
                    $somme += $cadeau->getPrix();
                }
            } else {
                foreach ($cadeaux as $cadeau) {
                    $cadeau->setPrix($cadeau->getPrix() - ($cadeau->getPrix() * ($data["pourcentage"] / 100)));
                    $somme += $cadeau->getPrix();
                }
            }
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->json(["moyenne" => number_format($somme / $cadeaux->count(), 2)], 200);
        } else {
            return $this->json(["fail" => 'La categorie a modifier n\'est pas retrouvé'], 422);
        }
    }
    /**
     * @Route("/categories/ajouter", name="ajouter_categories", methods={"POST"})
     */
    public function ajouterCategorie(Request $request, ValidatorInterface $validator)
    {
        $data = $this->getJson($request);
        $categorie = new Categories();
        $categorie->setNom($data["nom"]);
        if(count($validator->validate($categorie)) > 0){
            return $this->json(["fail" => "Donnée invalide"], 422);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($categorie);
        $em->flush();
        $cadeaux = $categorie->getCadeaux();
        $moyenne = 0;
        if($cadeaux->count() > 0) {
            $i = 0;
            foreach ($cadeaux as $cadeau) {
                $moyenne += $cadeau->getPrix();
                $i++;
            }
            $moyenne = $moyenne / $i;
        }
        return $this->json([
            "id" => $categorie->getId(),
            "nom" => $categorie->getNom(),
            "moyenne" => number_format($moyenne, 2),
            ], 200);
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
     * @Route("/categories/filtre/age", name="filtre_age_categories", methods={"GET"})
     */
    public function filtreAgeCategorie(Request $request)
    {
        $form = $this->createForm(CategroriesType::class);
        $data = $request->query;
        $repository = $this->getDoctrine()->getRepository(Personnes::class);
        $persones = $repository->findPersoneFiltreAge($data->get('min'),$data->get('max'));
        $categories = [];
        $tabCategories = [];
        foreach ($persones as $persone) {
            $cadeaux = $persone->getCadeaux();
            foreach ($cadeaux as $cadeau) {
                if(!in_array($cadeau->getCategorie(), $categories)){
                    array_push($categories, $cadeau->getCategorie());
                }
            }
            $i = 0;
            $somme = 0;
            foreach ($categories as $categorie){
                $cadeaux = $categorie->getCadeaux();
                foreach ($cadeaux as $cadeau){
                    $somme += $cadeau->getPrix();
                }
                $tabCategories[$i] = [
                    'id' => $categorie->getId(),
                    'nom' => $categorie->getNom(),
                    'moyenne' => number_format(($somme / $cadeaux->count()), 2)
                ];
                $i++;
            }

        }

        return $this->json($tabCategories, 200);
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
