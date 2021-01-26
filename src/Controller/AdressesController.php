<?php

namespace App\Controller;


use App\Entity\Adresses;
use App\Form\AdressesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Dotenv\Exception\FormatException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
class AdressesController extends AbstractController
{
 /*   private $form;

    public function __construct()
    {
        $this->$form = $this->createForm(AdressesType::class, $adresse);
    }
*/
    /**
     * @Route("/adresses", name="adresses")
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(AdressesType::class, null);
        if ($request->isXmlHttpRequest()) {
            $adresses = $this->getDoctrine()->getRepository(Adresses::class)->findAll();
            $i = 0;
            $dataJson = [];
            foreach ($adresses as $adresse) {
                $ligne = [
                    "id" => $adresse->getId(),
                    "nomRue" => $adresse->getNomRue(),
                    "numRue" => $adresse->getNumRue(),
                    "codePostal" => $adresse->getCodePostal(),
                    "ville" => $adresse->getVille(),
                ];
                $dataJson[$i] = $ligne;
                $i++;
            }
            return new JsonResponse($dataJson);
        } else {
            return $this->render('adresses/index.html.twig', ['adressesFormAjouter' => $form->createView(), 'adressesFormModifier' => $form->createView()]);
        }
    }

    /**
     * @Route("/adresses/ajouter", name="ajouter_adresses", methods={"POST"})
     */
    public function ajouterAction(Request $request): Response
    {
        if($request->isXmlHttpRequest()) {
            $json = $this->getJson($request);
            $form = $this->createForm(AdressesType::class, null);
            $form->submit($json);
            if (!$form->isValid()) {
                throw new FormatException($form);
            }
            $adresse = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($adresse);
            $em->flush();
            return new JsonResponse([
                ["success" => $adresse->getId()]
            ], 200);
        }
    }
    /**
     * @Route("/adresses/modifier/{id}", name="modifier_adresses", methods={"POST"})
     */
    public function modifierAction(Adresses $oldAdresses, Request $request): Response
    {
        if($request->isXmlHttpRequest() && $oldAdresses != null) {
            $json = $this->getJson($request);
            $form = $this->createForm(AdressesType::class, null);
            $form->submit($json);
            if (!$form->isValid()) {
                throw new FormatException($form);
            }
            /**
             * @var Adresses $newAdresse
             */
            $newAdresse = $form->getData();

            $oldAdresses->setNumRue($newAdresse->getNumRue())
                ->setNomRue($newAdresse->getNomRue())
                ->setVille($newAdresse->getVille())
                ->setCodePostal($newAdresse->getCodePostal());

            $em = $this->getDoctrine()->getManager();
            $em->persist($oldAdresses);
            $em->flush();

            return new JsonResponse([
                ["success" => $oldAdresses->getId()]
            ], 200);
        } else {
            return new JsonResponse([["fail" => "Action échoué"]], 412);
        }
    }

    /**
     * @Route("/adresses/supprimer/{id}", name="supprimer_adresses", methods={"DELETE"})
     */
    public function supprimerAction(Adresses $oldAdresses, Request $request)
    {
        if($request->isXmlHttpRequest() && $oldAdresses != null) {
            if (!$oldAdresses->getPersonnes()[0]) {
                $em = $this->getDoctrine()->getManager();
                $id = $oldAdresses->getId();
                $em->remove($oldAdresses);
                $em->flush();
                return new JsonResponse([["success" => $id]], 200);
            } else {
                return new JsonResponse([["message" => "Impossible de supprimer cette adresse ! L'adresse contient des personnes"]], 500);
            }
        } else {
            return new JsonResponse([["message" => "L'adresse à supprimer n'existe pas !"]], 404);
        }
    }
    /**
     * @Route("/adresses/ville", name="adresse_par_ville", methods={"GET", "POST"})
     */
    public function afficherAdressesParVille(Request $request){
        if($request->isXmlHttpRequest() || true)
        {
            $ville = $this->getJson($request)["ville"];
            if($ville != "") {
                $adresses = $this->getDoctrine()->getRepository(Adresses::class)->findBy(["ville" => $ville]);
            } else {
                $adresses = $this->getDoctrine()->getRepository(Adresses::class)->findAll();
            }
            $i = 0;
            $dataJson = [];
            foreach ($adresses as $adresse) {
                $ligne = [
                    "id" => $adresse->getId(),
                    "nomRue" => $adresse->getNomRue(),
                    "numRue" => $adresse->getNumRue(),
                    "codePostal" => $adresse->getCodePostal(),
                    "ville" => $adresse->getVille(),
                ];
                $dataJson[$i] = $ligne;
                $i++;
            }
            return new JsonResponse($dataJson);
        }
        return new JsonResponse([["fail" => "Ajax request required"]],400);
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