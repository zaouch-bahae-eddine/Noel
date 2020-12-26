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
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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
            /*$encoders =  [new JsonEncoder()];
            $normalizers = [new ObjectNormalizer()];
            $serializer = new Serializer($normalizers, $encoders);
            /** @var Adresses $adresse */
            /*$adresse = $serializer->deserialize($request->getContent(), Adresses::class, 'json');*/
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
    public function modifierAction( Adresses $oldAdresses, Request $request): Response
    {
        dd($oldAdresses);
        if($request->isXmlHttpRequest() && $oldAdresses != null) {
            $json = $this->getJson($request);
            $form = $this->createForm(AdressesType::class, null);
            $form->submit($json);
            if (!$form->isValid()) {
                throw new FormatException($form);
            }
            $newAdresse = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($newAdresse);
            $em->flush();
            return new JsonResponse([
                ["success" => $newAdresse->getId()]
            ], 200);
        } else {
            return new JsonResponse([
                ["fail" => 0]
            ], 412);
        }
    }
    private function getJson(Request $request)
    {

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpException(400, 'Invalid json');
        }
        //dd($data);
        return $data;
    }
}