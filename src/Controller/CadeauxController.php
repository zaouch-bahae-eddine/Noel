<?php

namespace App\Controller;

use App\Entity\Cadeaux;
use App\Form\CadeauxType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CadeauxController extends AbstractController
{
    /**
     * @Route("/cadeaux", name="cadeaux", methods={"GET"})
     * @
     */
    public function index(Request $request)
    {
        $form = $this->createForm(CadeauxType::class);
        $formModifier = $this->createForm(CadeauxType::class);
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
                    "idCategorie" => $cadeau->getCategorie()->getId(),
                ];
                $dataJson[$i] = $ligne;
                $i++;
            }
            return new JsonResponse($dataJson);
        } else {
            return $this->render('cadeaux/index.html.twig', [
                "cadeauxFormAjouter" => $form->createView(),
                "cadeauxFormModifier" => $formModifier->createView(),
            ]);
        }

    }
    /**
     * @Route("/cadeaux/ajouter", name="ajouter_cadeaux", methods={"POST"})
     */
    public function ajouterCadeaux(Request $request, ValidatorInterface $validator) {
        if( $request->isXmlHttpRequest()){
            $data = $this->getJson($request);
            $form = $this->createForm(CadeauxType::class);
            $form->submit($data);
            $cadeau = $form->getData();
            if(count($validator->validate($cadeau)) > 0){
                return  $this->json(["fail" => "Données du cadeau invalide !"], 422);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($cadeau);
            $em->flush();
            return new JsonResponse([["success" => $cadeau->getId()]]);
        } else {
            return $this->json(["fail" => "Un probleme de connexion peut etre la cause !"], 500);
        }
    }
    /**
     * @Route("/cadeaux/modifier/{id}", name="modifier_cadeaux", methods={"POST"})
     */
    public function modifierCadeau(Request $request, Cadeaux $cadeau, ValidatorInterface $validator)
    {
        if ($request->isXmlHttpRequest()){
            if ($cadeau != null) {
                $data = $this->getJson($request);
                $form = $this->createForm(CadeauxType::class);
                $form->submit($data);
                $newCadeau = $form->getData();
                if (count($validator->validate($newCadeau)) > 0) {
                    return $this->json(["fail" => "Données du cadeau invalide !"], 422);
                }
                $cadeau->setNom($newCadeau->getNom())
                    ->setAge($newCadeau->getAge())
                    ->setPrix($newCadeau->getPrix())
                    ->setCategorie($newCadeau->getCategorie());
                return new JsonResponse([["success" => $cadeau->getId()]],200);
            } else {
                return new JsonResponse([["fail" => "cadeau à modifier n'existe pas ! il peut etre supprimé"]],404);
            }
        } else {
            return new JsonResponse([["fail" => "Un probleme de connexion peut etre la cause !"]],500);
        }
    }
    /**
     * @Route("/cadeaux/supprimer/{id}", name="supprimer_cadeaux", methods={"DELETE"})
     */
    public function supprimerCadeau(Request $request, Cadeaux $cadeau, ValidatorInterface $validator)
    {
        if ($request->isXmlHttpRequest()){
            if ($cadeau != null) {
                if(count($cadeau->getPersonnes()) == 0) {
                    $idCadeau = $cadeau->getId();
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($cadeau);
                    $em->flush();
                    return new JsonResponse([["success" =>$idCadeau ]],200);
                } else {
                    return new JsonResponse([["fail" => "Cadeau est souhaité par des personnes !"]], 500);
                }
            } else {
                return new JsonResponse([["fail" => "Cadeau à supprimer n'existe pas ! il peut etre deja supprimé"]], 404);
            }
        }else {
            return new JsonResponse([["fail" => "request not XMLHTTPREQUEST"]], 500);
        }
    }
    /**
     * @Route("/cadeaux/personnes/{id}", name="personnes_cadeaux", methods={"GET"})
     */
    public function personnesCadeau(Request $request, Cadeaux $cadeau, ValidatorInterface $validator)
    {
        if ($request->isXmlHttpRequest()){
            if ($cadeau != null) {
                $personnes = $cadeau->getPersonnes();
                $tabPersonne = [];
                $i = 0;
                $nbHomme = 0;
                $nbFemme = 0;
                foreach ($personnes as $personne){
                    $tabPersonne[$i]= ["nom" =>$personne->getNom()];
                    if($personne->getSexe() == 'Homme') {
                        $nbHomme++;
                    }else {
                        $nbFemme++;
                    }
                        $i++;
                }
                $tabPersonneStatSexe = [
                    "Personne" => $tabPersonne,
                    "statSexe" => ["Homme" => $nbHomme, "Femme" => $nbFemme]
                ];
                return new JsonResponse($tabPersonneStatSexe);
            }else {
                return new JsonResponse([["fail" => "cadeau non trouvable"]], 404);
            }
        }else {
            return new JsonResponse([["fail" => "request not XMLHTTPREQUEST"]], 500);
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
